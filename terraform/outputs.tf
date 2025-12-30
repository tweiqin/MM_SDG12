output "alb_dns_name" {
  value = module.public_alb.alb_dns_name
}

output "rds_endpoint" {
  value = module.database.db_endpoint
}

output "s3_bucket_name" {
  value = module.storage.bucket_name
}

output "db_schema_s3_key" {
  value = module.storage.sql_file_key
}

output "db_password_secret_name" {
  value = module.secrets.secret_name
}


output "cloudfront_domain_name" {
  description = "The Secure HTTPS URL for the website"
  value       = module.cloudfront.cloudfront_domain_name
}
