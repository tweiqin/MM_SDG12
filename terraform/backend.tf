terraform {
  backend "s3" {
    bucket         = "makanmystery-tf-state"
    key            = "terraform.tfstate"
    region         = "ap-southeast-1"
    dynamodb_table = "makanmystery-terraform-lock"
    encrypt        = true
  }
}
