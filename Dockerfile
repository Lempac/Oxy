# ── Stage 1: Build Vue SPA & TypeScript Hooks ─────────────────────────────────
FROM node:24-alpine AS build
WORKDIR /app
RUN npm install -g pnpm@11.18.0 esbuild

COPY package.json pnpm-lock.yaml ./
RUN pnpm install --frozen-lockfile --ignore-scripts

COPY . .
RUN pnpm run build

# ── Stage 2: PocketBase Runtime ───────────────────────────────────────────────
FROM alpine:latest AS runtime
RUN apk add --no-cache ca-certificates unzip wget curl sqlite

WORKDIR /pb

# Download PocketBase binary
ARG PB_VERSION=0.22.28
ARG TARGETARCH
RUN ARCH="${TARGETARCH:-arm64}" && \
    case "$ARCH" in \
        "amd64") ARCH="amd64" ;; \
        "arm64"|"aarch64") ARCH="arm64" ;; \
    esac && \
    wget https://github.com/pocketbase/pocketbase/releases/download/v${PB_VERSION}/pocketbase_${PB_VERSION}_linux_${ARCH}.zip \
    && unzip pocketbase_${PB_VERSION}_linux_${ARCH}.zip \
    && rm pocketbase_${PB_VERSION}_linux_${ARCH}.zip

# Copy built Vue SPA to pb_public and TS compiled hooks to pb_hooks
COPY --from=build /app/dist /pb/pb_public
COPY --from=build /app/pb_hooks /pb/pb_hooks
COPY --from=build /app/pb_migrations /pb/pb_migrations

EXPOSE 8090

HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
    CMD curl --fail http://localhost:8090/api/health || exit 1

ENTRYPOINT ["/pb/pocketbase", "serve", "--http=0.0.0.0:8090", "--dir=/pb/pb_data"]
