#!/bin/sh
openapi-generator-cli generate -i https://api.flyo.cloud/nitro/openapi \
    -g php \
    --additional-properties=invokerPackage=Flyo