terraform {
  backend "s3" {
    # Replace these with your actual bucket details
    bucket         = "your-terraform-state-bucket"
    key            = "mm-sdg12/terraform.tfstate"
    region         = "us-east-1"
    
    # Optional: Enable state locking with DynamoDB
    # dynamodb_table = "terraform-state-lock"
    # encrypt        = true
  }
}
