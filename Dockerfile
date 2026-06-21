FROM ubuntu:latest
LABEL authors="lempac"

ENTRYPOINT ["top", "-b"]
