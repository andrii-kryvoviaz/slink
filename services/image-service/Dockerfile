FROM golang:1.23-alpine AS builder

RUN apk add --no-cache gcc musl-dev pkgconfig vips-dev vips-heif vips-jxl

WORKDIR /workspace

COPY proto/gen/go/ ./proto/gen/go/

RUN mkdir -p services/image-service

COPY services/image-service/go.mod services/image-service/go.sum ./services/image-service/

WORKDIR /workspace/services/image-service

RUN go mod download

COPY services/image-service/ ./

RUN CGO_ENABLED=1 go build -o image-service ./cmd

FROM alpine:latest

RUN apk --no-cache add vips vips-heif vips-jxl vips-tools

WORKDIR /app

COPY --from=builder /workspace/services/image-service/image-service .

EXPOSE 50051

CMD ["./image-service"]
