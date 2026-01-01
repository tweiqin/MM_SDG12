# MakanMystery (MM_SDG12) - tweiqin
Web Application

## 🛠 Tech Stack & Tools Used

### Frontend
- **HTML5, CSS3, JavaScript**: Core web technologies.
- **Bootstrap**: CSS framework for responsive design.

### Backend
- **PHP**: Server-side scripting language.
- **MySQL**: Relational database management system.

### DevOps & Cloud
- **Docker & Docker Compose**: Containerization for consistent development environments.
- **AWS (Amazon Web Services)**: Cloud infrastructure (EC2, RDS, ALB, ASG).
- **Terraform**: Infrastructure as Code (IaC) for AWS resource provisioning.
- **OneDev / GitHub Actions**: CI/CD pipelines (based on project structure).

### Development Tools
- **VS Code**: Recommended IDE.
- **XAMPP**: (Optional) For local non-Docker development.

## 💻 Local Testing / Development

To run the application locally, it is recommended to use Docker.

### Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed.
- Git installed.

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/tweiqin/MM_SDG12.git
   cd MM_SDG12
   ```

2. **Environment Configuration**
   - Create a `.env` file in the root directory. You can copy the template if available or set the following variables:
     ```env
     DB_HOST=db
     DB_NAME=makanmystery_db
     DB_USER=user
     DB_PASSWORD=password
     CHATBOT_API_KEY=your_api_key_here
     ```

3. **Start the Application**
   Run the following command to build and start the containers:
   ```bash
   docker-compose up -d --build
   ```

4. **Access the Application**
   - **Main App**: Open [http://localhost:8080](http://localhost:8080) in your browser.
   - **phpMyAdmin**: Open [http://localhost:8081](http://localhost:8081) to manage the database.

5. **Stop the Application**
   ```bash
   docker-compose down
   ```

## ☁️ Cloud Deployment (AWS)

The project includes Terraform configuration to deploy the infrastructure on AWS.

### Prerequisites
- AWS CLI installed and configured with appropriate permissions.
- Terraform installed.

### Deployment Steps

1. **Navigate to Terraform Directory**
   ```bash
   cd terraform
   ```

2. **Initialize Terraform**
   Initialize the working directory and download necessary providers.
   ```bash
   terraform init
   ```

3. **Plan Infrastructure**
   Preview the changes that Terraform will make.
   ```bash
   terraform plan
   ```
   *Note: You may be prompted for variables such as database passwords if they are not set in a `*.tfvars` file.*

4. **Apply Infrastructure**
   Provision the resources on AWS.
   ```bash
   terraform apply
   ```
   Type `yes` when prompted to confirm.

5. **Access Cloud Instance**
   After successful deployment, Terraform will output the Load Balancer DNS name or the EC2 public IP. Use that address to access the live application.

## 📦 CI/CD with GitHub Actions

This repository includes configured GitHub Actions workflows for automated deployment and teardown. If you fork or clone this repository, you can leverage these workflows.

### Repository Secrets Needed
To make the workflows function correctly, you must configure the following **Repository Secrets** in your GitHub repository settings (`Settings > Security > Secrets and variables > Actions > Repository secrets`):

| Secret Name | Description |
|---|---|
| `AWS_ACCESS_KEY_ID` | Your AWS Access Key ID. |
| `AWS_SECRET_ACCESS_KEY` | Your AWS Secret Access Key. |
| `AWS_REGION` | The AWS region to deploy to (e.g., `ap-southeast-1`). |
| `DB_HOST` | Database host endpoint (if applicable/external). |
| `DB_USERNAME` | Master username for the RDS instance. |
| `DB_PASSWORD` | Master password for the RDS database. |
| `DB_SECRET_NAME` | Name of the secret in AWS Secrets Manager (if used). |
| `DOCKERHUB_USERNAME` | Your Docker Hub username. |
| `DOCKERHUB_TOKEN` | Your Docker Hub access token. |
| `GEMINI_API_KEY` | API key for the Google Gemini chatbot feature. |

### Available Workflows
- **1MM - Build and Deploy** (`MM_Deploy.yml`): Full deployment pipeline. Builds the Docker image, pushes to Docker Hub and applies Terraform configuration.
- **2MM - Database Migration** (`MM_DB_Migrate.yml`): Executes database migration scripts on the EC2 instance via AWS SSM. Requires manual confirmation input.
- **3MM - Terraform Unlock State** (`MM_Unlock.yml`): Helper workflow to force-unlock the Terraform state if it becomes locked. Requires the Lock ID, need to run 2MM to get the ID.
- **4MM - Terraform Deploy** (`MM_tf_Deploy.yml`): Infrastructure-only deployment. Runs `terraform apply` without rebuilding the Docker image.
- **5MM - Destroy Infrastructure** (`MM_Destroy.yml`): Runs `terraform destroy` to tear down all provisioned AWS resources.


## 📂 Project Structure
- `/admin` - Admin dashboard files.
- `/api` - API endpoints (including Chatbot).
- `/assets` - Static assets (CSS, JS, Images).
- `/buyer` & `/seller` - Specific workflows for different user roles (consumer & vendor).
- `/config` - Database connections and configuration.
- `/terraform` - Infrastructure as Code definitions.
- `docker-compose.yml` - Docker service definitions.