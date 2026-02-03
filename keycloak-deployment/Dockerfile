# Dockerfile Keycloak Production pour Cloud Run
FROM quay.io/keycloak/keycloak:25.0.0 AS builder

# Enable health and metrics support
ENV KC_HEALTH_ENABLED=true
ENV KC_METRICS_ENABLED=true

# Configure database vendor
ENV KC_DB=postgres

WORKDIR /opt/keycloak

# Build optimized image
RUN /opt/keycloak/bin/kc.sh build

FROM quay.io/keycloak/keycloak:25.0.0

# Copy built artifacts from builder
COPY --from=builder /opt/keycloak/ /opt/keycloak/

# Set production environment
ENV KC_HOSTNAME_STRICT=false
ENV KC_PROXY=edge
ENV KC_HTTP_ENABLED=true
ENV KC_HEALTH_ENABLED=true
ENV KC_METRICS_ENABLED=true

# Expose port 8080 (Cloud Run requirement)
EXPOSE 8080

# Start Keycloak in production mode (combined CMD for Cloud Run compatibility)
CMD ["/opt/keycloak/bin/kc.sh", "start", "--optimized"]
