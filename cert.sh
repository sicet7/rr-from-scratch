#!/bin/sh
rm "./app.crt" "./app.key" "./root.srl" "./root.key" "./root.crt" "./app.csr"
openssl genrsa -out "./root.key" 4096 2>/dev/null
openssl req -x509 -new -nodes -key "./root.key" -sha256 -days 1024 \
    -subj "/C=DK/ST=Fyn/L=Odense/O=MRSINC/OU=MRSINC/CN=localhost" -out "./root.crt"
openssl genrsa -out "./app.key" 2048 2>/dev/null
openssl req -new -sha256 -key "./app.key" -subj "/C=DK/ST=Fyn/L=Odense/O=MRSINC/OU=MRSINC/CN=localhost" -out "./app.csr"
openssl x509 -req -in "./app.csr" -CA "./root.crt" -CAkey "./root.key" -CAcreateserial -out "./app.crt" -days 500 -sha256
rm "./app.csr" "./root.srl"