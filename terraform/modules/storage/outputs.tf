output "bucket_id" {
  value = aws_s3_bucket.static_assets.id
}

output "bucket_regional_domain_name" {
  value = aws_s3_bucket.static_assets.bucket_regional_domain_name
}

output "bucket_name" {
  value = aws_s3_bucket.static_assets.id
}

output "sql_file_key" {
  value = var.upload_sql ? aws_s3_object.sql_file[0].key : ""
}
