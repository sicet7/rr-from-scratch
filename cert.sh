#!/bin/sh
rm -rf "./certs"
mkdir -p "./certs"
openssl genrsa -out "./certs/root.key" 4096 2>/dev/null
openssl genrsa -out "./certs/oauth.key" 4096 2>/dev/null
openssl rsa -in "./certs/oauth.key" -pubout -out "./certs/oauth.pub"
openssl req -x509 -new -nodes -key "./certs/root.key" -sha256 -days 1024 \
    -subj "/C=DK/ST=Fyn/L=Odense/O=MRSINC/OU=MRSINC/CN=localhost" -out "./certs/root.crt"
openssl genrsa -out "./certs/app.key" 2048 2>/dev/null
openssl req -new -sha256 -key "./certs/app.key" -subj "/C=DK/ST=Fyn/L=Odense/O=MRSINC/OU=MRSINC/CN=localhost" -out "./certs/app.csr"
openssl x509 -req -in "./certs/app.csr" -CA "./certs/root.crt" -CAkey "./certs/root.key" -CAcreateserial -out "./certs/app.crt" -days 500 -sha256
rm "./certs/app.csr" "./certs/root.srl"