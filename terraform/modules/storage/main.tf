resource "aws_s3_bucket" "static_assets" {
  bucket_prefix = "${var.project_name}-assets-"
  force_destroy = true

  tags = {
    Name    = "${var.project_name}-assets"
    Project = var.project_name
  }
}

resource "aws_s3_bucket_public_access_block" "static_assets" {
  bucket = aws_s3_bucket.static_assets.id

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

resource "aws_s3_object" "assets" {
  for_each = fileset(var.assets_dir, "**/*")

  bucket = aws_s3_bucket.static_assets.id
  key    = "images/${each.value}"
  source = "${var.assets_dir}/${each.value}"

  etag = filemd5("${var.assets_dir}/${each.value}")

  content_type = lookup({
    ".html" = "text/html"
    ".css"  = "text/css"
    ".js"   = "application/javascript"
    ".png"  = "image/png"
    ".jpg"  = "image/jpeg"
    ".jpeg" = "image/jpeg"
    ".gif"  = "image/gif"
    ".svg"  = "image/svg+xml"
    ".webp" = "image/webp"
    ".avif" = "image/avif"
  }, regex("\\.[^.]+$", each.value), "application/octet-stream")
}

resource "aws_s3_object" "sql_file" {
  count = var.upload_sql ? 1 : 0

  bucket = aws_s3_bucket.static_assets.id
  key    = "db/schema.sql"
  source = var.sql_file_path

  etag = filemd5(var.sql_file_path)
}
