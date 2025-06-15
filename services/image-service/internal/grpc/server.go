package grpcserver

import (
	"log"
	"net"

	imagepb "github.com/andrii-kryvoviaz/slink-proto/slink.image"
	"google.golang.org/grpc"
)

func StartGRPCServer(handler imagepb.ImageServiceServer, addr string) error {
	lis, err := net.Listen("tcp", addr)
	if err != nil {
		return err
	}
	grpcServer := grpc.NewServer()
	imagepb.RegisterImageServiceServer(grpcServer, handler)
	log.Printf("gRPC server listening at %s", addr)
	return grpcServer.Serve(lis)
}
