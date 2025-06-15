package image

import (
	"errors"
	"fmt"

	imagepb "github.com/andrii-kryvoviaz/slink-proto/slink.image"
	"github.com/h2non/bimg"
)

func BuildOptions(req *imagepb.ImageRequest) (*bimg.Options, error) {
	opts := &bimg.Options{}

	if req.Quality != nil {
		quality := int(*req.Quality)
		if quality < 1 || quality > 100 {
			return nil, errors.New("quality must be between 1 and 100")
		}
		opts.Quality = quality
	}

	opts.StripMetadata = req.StripMetadata
	
	
	
	if req.PreserveAnimation {
		
		
		
		
	}
	
	switch op := req.Operation.Op.(type) {
	case *imagepb.ImageOperation_Resize:
		opts.Width = int(op.Resize.Width)
		opts.Height = int(op.Resize.Height)
		opts.Enlarge = op.Resize.AllowEnlarge
		
		switch op.Resize.Mode {
		case imagepb.ResizeMode_EXACT:
			opts.Force = true
		case imagepb.ResizeMode_FIT:
		case imagepb.ResizeMode_SHORT_SIDE:
			opts.Force = false
		case imagepb.ResizeMode_LONG_SIDE:
			opts.Force = false
		case imagepb.ResizeMode_FILL:
			opts.Crop = true
		case imagepb.ResizeMode_STRETCH:
			opts.Force = true
			opts.NoAutoRotate = true
		}
		
		switch op.Resize.Filter {
		case imagepb.InterpolationFilter_LANCZOS:
		case imagepb.InterpolationFilter_BILINEAR:
		case imagepb.InterpolationFilter_BICUBIC:
		case imagepb.InterpolationFilter_NEAREST:
		default:
		}
		
	case *imagepb.ImageOperation_Crop:
		
		
		
		opts.Width = int(op.Crop.Width)
		opts.Height = int(op.Crop.Height)
		opts.Crop = true
		
		if op.Crop.X != nil && op.Crop.Y != nil {
			opts.Left = int(*op.Crop.X)
			opts.Top = int(*op.Crop.Y)
		} else {
			
			switch op.Crop.Position {
			case imagepb.CropPosition_CENTER:
				opts.Gravity = bimg.GravityCentre
			case imagepb.CropPosition_TOP_LEFT:
				opts.Gravity = bimg.GravityNorth
			case imagepb.CropPosition_TOP:
				opts.Gravity = bimg.GravityNorth
			case imagepb.CropPosition_TOP_RIGHT:
				opts.Gravity = bimg.GravityNorth
			case imagepb.CropPosition_LEFT:
				opts.Gravity = bimg.GravityWest
			case imagepb.CropPosition_RIGHT:
				opts.Gravity = bimg.GravityEast
			case imagepb.CropPosition_BOTTOM_LEFT:
				opts.Gravity = bimg.GravitySouth
			case imagepb.CropPosition_BOTTOM:
				opts.Gravity = bimg.GravitySouth
			case imagepb.CropPosition_BOTTOM_RIGHT:
				opts.Gravity = bimg.GravitySouth
			}
		}
		
	case *imagepb.ImageOperation_Rotate:
		opts.Rotate = bimg.Angle(op.Rotate.Angle)
		if op.Rotate.BackgroundColor != nil {
			color := op.Rotate.BackgroundColor
			opts.Background = bimg.Color{
				R: uint8(color.Red),
				G: uint8(color.Green),
				B: uint8(color.Blue),
			}
		}
		
	case *imagepb.ImageOperation_Gamma:
		opts.Gamma = float64(op.Gamma.Gamma)
		
	case *imagepb.ImageOperation_Blur:
		opts.GaussianBlur = bimg.GaussianBlur{
			Sigma: float64(op.Blur.Sigma),
		}
		
	case *imagepb.ImageOperation_Sharpen:
		opts.Sharpen = bimg.Sharpen{
			Radius: 1,
			X1:     float64(op.Sharpen.Sigma),
			Y2:     float64(op.Sharpen.Sigma),
		}
		
	case *imagepb.ImageOperation_Brightness:
		opts.Brightness = float64(op.Brightness.Brightness)
		
	case *imagepb.ImageOperation_Contrast:
		opts.Contrast = float64(op.Contrast.Contrast)
		
	case *imagepb.ImageOperation_Flip:
		opts.Flip = true
		
	case *imagepb.ImageOperation_Flop:
		opts.Flop = true
		
	case *imagepb.ImageOperation_Grayscale:
		opts.Interpretation = bimg.InterpretationBW
		
	case *imagepb.ImageOperation_Composite:
		return nil, errors.New("composite operations require separate processing")
		
	default:
		return nil, errors.New("unsupported operation")
	}

	return opts, nil
}

func ValidateImageFormat(format string) error {
	supportedFormats := map[string]bool{
		"jpeg": true, "jpg": true, "png": true, "webp": true,
		"tiff": true, "gif": true, "bmp": true, "svg": true,
		"pdf": true, "heif": true, "avif": true,
	}
	
	if !supportedFormats[format] {
		return fmt.Errorf("unsupported format: %s", format)
	}
	return nil
}

func GetBimgType(format string) bimg.ImageType {
	switch format {
	case "jpeg", "jpg":
		return bimg.JPEG
	case "png":
		return bimg.PNG
	case "webp":
		return bimg.WEBP
	case "tiff":
		return bimg.TIFF
	case "gif":
		return bimg.GIF
	case "svg":
		return bimg.SVG
	case "pdf":
		return bimg.PDF
	case "heif":
		return bimg.HEIF
	case "avif":
		return bimg.AVIF
	default:
		return bimg.JPEG
	}
}
