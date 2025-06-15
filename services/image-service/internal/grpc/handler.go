package grpcserver

import (
	"context"

	"github.com/andrii-kryvoviaz/slink-image/internal/image"
	imagepb "github.com/andrii-kryvoviaz/slink-proto/slink.image"
)

type ImageServiceHandler struct {
	proc image.Processor
	imagepb.UnimplementedImageServiceServer
}

func NewImageServiceHandler(proc image.Processor) *ImageServiceHandler {
	return &ImageServiceHandler{proc: proc}
}

func (h *ImageServiceHandler) ProcessImage(ctx context.Context, req *imagepb.ImageRequest) (*imagepb.ImageResponse, error) {
	return h.proc.Process(req)
}

func (h *ImageServiceHandler) GetImageInfo(ctx context.Context, req *imagepb.ImageInfoRequest) (*imagepb.ImageInfoResponse, error) {
	return h.proc.GetImageInfo(req)
}

func (h *ImageServiceHandler) ValidateImage(ctx context.Context, req *imagepb.ImageValidationRequest) (*imagepb.ImageValidationResponse, error) {
	return h.proc.ValidateImage(req)
}
