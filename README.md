Cloud Events Web UI
===

## Install with Docker Compose

1. clone the repository
    ```shell
    git clone https://github.com/gplanchat/cloud-events-webui.git
    ```
2. start the stack
    ```shell
    docker compose up -d
    ```

## Install with Helm in a Kubernetes cluster

1. clone the repository
    ```shell
    git clone https://github.com/gplanchat/cloud-events-webui.git
    ```
   2. deploy in your Kubernetes cluster
       ```shell
       helm dependencies update ./helm/cloudevents-webui
       helm lint ./helm/cloudevents-webui
       helm upgrade main ./helm/cloudevents-webui --namespace=default --create-namespace --wait \
       --install \
       --set "php.image.repository=gcr.io/test-cloudevents-webui/php" \
       --set php.image.tag=latest \
       --set "pwa.image.repository=gcr.io/test-cloudevents-webui/pwa" \
       --set pwa.image.tag=latest \
       --set php.appSecret='!ChangeMe!' \
       --set postgresql.postgresqlPassword='!ChangeMe!' \
       --set postgresql.persistence.enabled=true \
       --set "corsAllowOrigin=^https?:\/\/[a-z]*\.mywebsite.com$"
       ```

For more details, check the [API Platform official documentation](https://api-platform.com/docs/deployment/kubernetes/)

## Install with Skaffold in a Kubernetes cluster

1. clone the repository
    ```shell
    git clone https://github.com/gplanchat/cloud-events-webui.git
    ```
2. start the stack in your Kubernetes cluster
    ```shell
    cd helm/
    skaffold dev
    ```

For more details, check the [API Platform official documentation](https://api-platform.com/docs/deployment/minikube/)
