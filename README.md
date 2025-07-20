### API Routes

Your application exposes the following API routes, accessible via the Nginx proxy on port `7071` of your host machine.

**Base URL:** `http://localhost:7071`

---

#### 1. Start a Workflow

* **Endpoint:** `/start-workflow`

* **Method:** `GET`

* **Description:** This route initiates an example workflow within your application. Upon successful execution, it will typically return a unique ID for the started workflow.

* **Example Request (using `curl`):**

    ```bash
    curl http://localhost:7071/start-workflow
    ```

* **Expected Response (Example):**

    ```json
    {
        "message": "Workflow started successfully",
        "workflow_id": "some-unique-workflow-id"
    }
    ```

    (The actual response may vary based on your `WorkflowController` implementation.)

---

#### 2. Get Workflow Status

* **Endpoint:** `/workflow-status/{id}`

* **Method:** `GET`

* **Description:** This route allows you to retrieve the current status of a specific workflow using its unique ID.

* **Parameters:**

    * `id` (Path Parameter): The unique ID of the workflow you want to check. This ID is obtained from the `/start-workflow` endpoint.

* **Example Request (using `curl`):**

    ```bash
    curl http://localhost:7071/workflow-status/some-unique-workflow-id
    ```

    (Replace `some-unique-workflow-id` with an actual ID returned from `/start-workflow`.)

* **Expected Response (Example):**

    ```json
    {
        "workflow_id": "some-unique-workflow-id",
        "status": "completed",
        "details": "..."
    }
    ```

    (The actual response may vary based on your `WorkflowController` implementation.)

---

**To use these routes:**

1.  Ensure your Docker services are up and running:

    ```bash
    docker compose up -d
    ```

2.  Open your web browser or use a tool like `curl` or Postman to make requests to the specified endpoints on `http://localhost:7071`.