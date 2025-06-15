package image

import (
	"fmt"
	"log"
	"strconv"
	"strings"
	"time"

	imagepb "github.com/andrii-kryvoviaz/slink-proto/slink.image"
	"github.com/h2non/bimg"
)

type Processor interface {
	Process(*imagepb.ImageRequest) (*imagepb.ImageResponse, error)
	GetImageInfo(*imagepb.ImageInfoRequest) (*imagepb.ImageInfoResponse, error)
	ValidateImage(*imagepb.ImageValidationRequest) (*imagepb.ImageValidationResponse, error)
}

type BimgProcessor struct{}

func NewBimgProcessor() *BimgProcessor {
	major, minor, patch := getLibvipsVersion()
	log.Printf("Initialized bimg processor with libvips %d.%d.%d", major, minor, patch)
	return &BimgProcessor{}
}

func (p *BimgProcessor) Process(req *imagepb.ImageRequest) (*imagepb.ImageResponse, error) {
	startTime := time.Now()
	
	if err := ValidateImageFormat(req.InputType); err != nil {
		return &imagepb.ImageResponse{
			Result: &imagepb.ImageResponse_Error{
				Error: &imagepb.ProcessingError{
					Code:    imagepb.ErrorCode_INVALID_INPUT_FORMAT,
					Message: err.Error(),
				},
			},
		}, nil
	}
	
	if err := ValidateImageFormat(req.OutputType); err != nil {
		return &imagepb.ImageResponse{
			Result: &imagepb.ImageResponse_Error{
				Error: &imagepb.ProcessingError{
					Code:    imagepb.ErrorCode_UNSUPPORTED_OUTPUT_FORMAT,
					Message: err.Error(),
				},
			},
		}, nil
	}
	
	img := bimg.NewImage(req.ImageData)
	originalSize, err := img.Size()
	if err != nil {
		return &imagepb.ImageResponse{
			Result: &imagepb.ImageResponse_Error{
				Error: &imagepb.ProcessingError{
					Code:    imagepb.ErrorCode_PROCESSING_FAILED,
					Message: fmt.Sprintf("failed to get image size: %v", err),
				},
			},
		}, nil
	}
	
	var newImg []byte
	
	newImg, err = p.processWithBimg(img, req)
	
	if err != nil {
		return &imagepb.ImageResponse{
			Result: &imagepb.ImageResponse_Error{
				Error: &imagepb.ProcessingError{
					Code:    imagepb.ErrorCode_PROCESSING_FAILED,
					Message: fmt.Sprintf("image processing failed: %v", err),
				},
			},
		}, nil
	}
	
	newSize, err := bimg.NewImage(newImg).Size()
	if err != nil {
		return &imagepb.ImageResponse{
			Result: &imagepb.ImageResponse_Error{
				Error: &imagepb.ProcessingError{
					Code:    imagepb.ErrorCode_PROCESSING_FAILED,
					Message: fmt.Sprintf("failed to get processed image size: %v", err),
				},
			},
		}, nil
	}
	
	processingTime := time.Since(startTime)
	compressionRatio := float32(len(req.ImageData)) / float32(len(newImg))
	
	return &imagepb.ImageResponse{
		Result: &imagepb.ImageResponse_Success{
			Success: &imagepb.ProcessedImage{
				ImageData:        newImg,
				OutputType:       req.OutputType,
				Width:           uint32(newSize.Width),
				Height:          uint32(newSize.Height),
				OriginalWidth:   uint32(originalSize.Width),
				OriginalHeight:  uint32(originalSize.Height),
				Metadata: &imagepb.ProcessingMetadata{
					ProcessingTimeMs:  processingTime.Milliseconds(),
					OutputSizeBytes:   int64(len(newImg)),
					CompressionRatio:  compressionRatio,
				},
			},
		},
	}, nil
}

func (p *BimgProcessor) GetImageInfo(req *imagepb.ImageInfoRequest) (*imagepb.ImageInfoResponse, error) {
	img := bimg.NewImage(req.ImageData)
	
	size, err := img.Size()
	if err != nil {
		return &imagepb.ImageInfoResponse{
			Result: &imagepb.ImageInfoResponse_Error{
				Error: &imagepb.ProcessingError{
					Code:    imagepb.ErrorCode_PROCESSING_FAILED,
					Message: fmt.Sprintf("failed to get image info: %v", err),
				},
			},
		}, nil
	}
	
	imgType := img.Type()
	
	info := &imagepb.ImageInfo{
		Format:     imgType,
		Width:      uint32(size.Width),
		Height:     uint32(size.Height),
		SizeBytes:  int64(len(req.ImageData)),
		HasAlpha:   false,
		IsAnimated: false,
		Metadata:   make(map[string]string),
	}
	
	return &imagepb.ImageInfoResponse{
		Result: &imagepb.ImageInfoResponse_Info{
			Info: info,
		},
	}, nil
}

func (p *BimgProcessor) ValidateImage(req *imagepb.ImageValidationRequest) (*imagepb.ImageValidationResponse, error) {
	img := bimg.NewImage(req.ImageData)
	
	var validationErrors []string
	
	size, err := img.Size()
	if err != nil {
		return &imagepb.ImageValidationResponse{
			IsValid:          false,
			ValidationErrors: []string{fmt.Sprintf("failed to read image: %v", err)},
		}, nil
	}
	
	if req.MaxWidth != nil && uint32(size.Width) > *req.MaxWidth {
		validationErrors = append(validationErrors, 
			fmt.Sprintf("image width %d exceeds maximum %d", size.Width, *req.MaxWidth))
	}
	if req.MaxHeight != nil && uint32(size.Height) > *req.MaxHeight {
		validationErrors = append(validationErrors, 
			fmt.Sprintf("image height %d exceeds maximum %d", size.Height, *req.MaxHeight))
	}
	
	if req.MaxSizeBytes != nil && int64(len(req.ImageData)) > *req.MaxSizeBytes {
		validationErrors = append(validationErrors, 
			fmt.Sprintf("image size %d bytes exceeds maximum %d bytes", 
				len(req.ImageData), *req.MaxSizeBytes))
	}
	
	imgType := img.Type()
	if len(req.AllowedFormats) > 0 {
		formatAllowed := false
		for _, allowedFormat := range req.AllowedFormats {
			if imgType == allowedFormat {
				formatAllowed = true
				break
			}
		}
		if !formatAllowed {
			validationErrors = append(validationErrors, 
				fmt.Sprintf("image format %s is not allowed", imgType))
		}
	}
	
	info := &imagepb.ImageInfo{
		Format:    imgType,
		Width:     uint32(size.Width),
		Height:    uint32(size.Height),
		SizeBytes: int64(len(req.ImageData)),
		HasAlpha:  false,
	}
	
	return &imagepb.ImageValidationResponse{
		IsValid:          len(validationErrors) == 0,
		ValidationErrors: validationErrors,
		Info:            info,
	}, nil
}

func (p *BimgProcessor) processWithBimg(img *bimg.Image, req *imagepb.ImageRequest) ([]byte, error) {
	
	if cropOp := req.Operation.GetCrop(); cropOp != nil {
		return p.processSmartCrop(img, req, cropOp)
	} else {
		opts, err := BuildOptions(req)
		if err != nil {
			return nil, err
		}
		
		opts.Type = GetBimgType(req.OutputType)
		return img.Process(*opts)
	}
}

func (p *BimgProcessor) processSmartCrop(img *bimg.Image, req *imagepb.ImageRequest, cropOp *imagepb.CropOptions) ([]byte, error) {
	originalSize, err := img.Size()
	if err != nil {
		return nil, err
	}
	
	targetWidth := int(cropOp.Width)
	targetHeight := int(cropOp.Height)
	
	
	if cropOp.X != nil && cropOp.Y != nil {
		opts := &bimg.Options{
			Left:         int(*cropOp.X),
			Top:          int(*cropOp.Y),
			Width:        targetWidth,
			Height:       targetHeight,
			Crop:         true,
			Type:         GetBimgType(req.OutputType),
			StripMetadata: req.StripMetadata,
		}
		
		if req.Quality != nil {
			opts.Quality = int(*req.Quality)
		}
		
		return img.Process(*opts)
	}
	
	
	originalWidth := float64(originalSize.Width)
	originalHeight := float64(originalSize.Height)
	targetW := float64(targetWidth)
	targetH := float64(targetHeight)
	
	
	sourceRatio := originalWidth / originalHeight
	targetRatio := targetW / targetH
	
	var firstStepOpts, secondStepOpts *bimg.Options
	
	if targetRatio < sourceRatio {
		
		resizeHeight := targetHeight
		resizeWidth := int(targetH * sourceRatio)
		
		
		firstStepOpts = &bimg.Options{
			Height:        resizeHeight,
			Width:         resizeWidth,
			Type:          GetBimgType(req.OutputType),
			StripMetadata: req.StripMetadata,
		}
		
		
		excessWidth := resizeWidth - targetWidth
		var cropLeft int
		
		switch cropOp.Position {
		case imagepb.CropPosition_LEFT, imagepb.CropPosition_TOP_LEFT, imagepb.CropPosition_BOTTOM_LEFT:
			cropLeft = 0
		case imagepb.CropPosition_RIGHT, imagepb.CropPosition_TOP_RIGHT, imagepb.CropPosition_BOTTOM_RIGHT:
			cropLeft = excessWidth
		default: 
			cropLeft = excessWidth / 2
		}
		
		secondStepOpts = &bimg.Options{
			Left:   cropLeft,
			Top:    0,
			Width:  targetWidth,
			Height: targetHeight,
			Crop:   true,
			Type:   GetBimgType(req.OutputType),
		}
		
	} else {
		
		resizeWidth := targetWidth
		resizeHeight := int(targetW / sourceRatio)
		
		
		firstStepOpts = &bimg.Options{
			Width:         resizeWidth,
			Height:        resizeHeight,
			Type:          GetBimgType(req.OutputType),
			StripMetadata: req.StripMetadata,
		}
		
		
		excessHeight := resizeHeight - targetHeight
		var cropTop int
		
		switch cropOp.Position {
		case imagepb.CropPosition_TOP, imagepb.CropPosition_TOP_LEFT, imagepb.CropPosition_TOP_RIGHT:
			cropTop = 0
		case imagepb.CropPosition_BOTTOM, imagepb.CropPosition_BOTTOM_LEFT, imagepb.CropPosition_BOTTOM_RIGHT:
			cropTop = excessHeight
		default: 
			cropTop = excessHeight / 2
		}
		
		secondStepOpts = &bimg.Options{
			Left:   0,
			Top:    cropTop,
			Width:  targetWidth,
			Height: targetHeight,
			Crop:   true,
			Type:   GetBimgType(req.OutputType),
		}
	}
	
	
	if req.Quality != nil {
		quality := int(*req.Quality)
		firstStepOpts.Quality = quality
		secondStepOpts.Quality = quality
	}
	
	
	
	resizedImg, err := img.Process(*firstStepOpts)
	if err != nil {
		return nil, fmt.Errorf("resize step failed: %v", err)
	}
	
	
	intermediateImg := bimg.NewImage(resizedImg)
	finalImg, err := intermediateImg.Process(*secondStepOpts)
	if err != nil {
		return nil, fmt.Errorf("crop step failed: %v", err)
	}
	
	return finalImg, nil
}

func getLibvipsVersion() (major, minor, patch int) {
	versionString := bimg.VipsVersion
	parts := strings.Split(versionString, ".")
	
	if len(parts) >= 3 {
		major, _ = strconv.Atoi(parts[0])
		minor, _ = strconv.Atoi(parts[1])
		patch, _ = strconv.Atoi(parts[2])
	}
	
	return major, minor, patch
}
