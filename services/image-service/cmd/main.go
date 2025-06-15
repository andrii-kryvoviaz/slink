package main

import (
	"log"
	"os"

	grpcserver "github.com/andrii-kryvoviaz/slink-image/internal/grpc"
	image "github.com/andrii-kryvoviaz/slink-image/internal/image"
)

func main() {
	addr := ":50051"
	if envAddr := os.Getenv("GRPC_ADDR"); envAddr != "" {
		addr = envAddr
	}
	proc := image.NewBimgProcessor()
	handler := grpcserver.NewImageServiceHandler(proc)
	if err := grpcserver.StartGRPCServer(handler, addr); err != nil {
		log.Fatalf("failed to start gRPC server: %v", err)
	}
}
