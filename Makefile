.PHONY: build fmt vet test tidy check

build:
	go build -o argtyper .

fmt:
	gofmt -w .

vet:
	go vet ./...

test:
	go test -race ./...

tidy:
	go mod tidy

check: vet build test
