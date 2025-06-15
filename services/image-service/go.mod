module github.com/andrii-kryvoviaz/slink-image

go 1.23.0

toolchain go1.24.3

require (
	github.com/andrii-kryvoviaz/slink-proto v0.0.0-00010101000000-000000000000
	github.com/h2non/bimg v1.1.9
	google.golang.org/grpc v1.73.0
)

replace github.com/andrii-kryvoviaz/slink-proto => ../../proto/gen/go

require (
	golang.org/x/net v0.38.0 // indirect
	golang.org/x/sys v0.31.0 // indirect
	golang.org/x/text v0.26.0 // indirect
	google.golang.org/genproto/googleapis/rpc v0.0.0-20250324211829-b45e905df463 // indirect
	google.golang.org/protobuf v1.36.6 // indirect
)
