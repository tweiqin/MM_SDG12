#!/bin/bash
set -e

# Update usage: ./db_migrate.sh <bucket_name> <s3_key> <region> <secret_name> <db_name> <db_host> <db_user>

BUCKET_NAME=$1
S3_KEY=$2
REGION=$3
SECRET_NAME=$4
DB_NAME=$5
DB_HOST=$6
DB_USER=$7

echo "Starting DB Migration..."

# 1. Install Dependencies
echo "Installing MySQL Client..."
apt-get update -y
apt-get install -y mysql-client awscli

# 2. Retrieve Password
echo "Retrieving DB Password..."
DB_PASSWORD=$(aws secretsmanager get-secret-value --secret-id $SECRET_NAME --query SecretString --output text --region $REGION)

# 3. Download SQL
echo "Downloading SQL file from s3://$BUCKET_NAME/$S3_KEY..."
aws s3 cp s3://$BUCKET_NAME/$S3_KEY /tmp/migration.sql

# 4. Run Migration
echo "Running SQL Import..."
mysql -h $DB_HOST -u $DB_USER -p"$DB_PASSWORD" $DB_NAME < /tmp/migration.sql

echo "Migration Complete."
