output "alb_dns_name" {
  value = module.public_alb.alb_dns_name
}

output "rds_endpoint" {
  value = module.database.db_endpoint
}



output "s3_bucket_name" {
  value = module.storage.bucket_name
}

