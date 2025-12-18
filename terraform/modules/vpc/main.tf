variable "project_name" {
  type    = string
  default = "mm-sdg12"
}

resource "aws_vpc" "main" {
  cidr_block           = "10.0.0.0/16"
  enable_dns_hostnames = true
  enable_dns_support   = true

  tags = {
    Name = "${var.project_name}-vpc"
  }
}

resource "aws_internet_gateway" "main" {
  vpc_id = aws_vpc.main.id

  tags = {
    Name = "${var.project_name}-igw"
  }
}

# --- Public Subnets ---
# Used for resources that need direct internet access (ALB, NAT Gateway)
resource "aws_subnet" "public_a" {
  vpc_id                  = aws_vpc.main.id
  cidr_block              = "10.0.1.0/24"
  availability_zone       = "ap-southeast-1a"
  map_public_ip_on_launch = true # Auto-assign Public IP

  tags = {
    Name = "${var.project_name}-public-subnet-1a"
  }
}

resource "aws_subnet" "public_b" {
  vpc_id                  = aws_vpc.main.id
  cidr_block              = "10.0.2.0/24"
  availability_zone       = "ap-southeast-1b"
  map_public_ip_on_launch = true

  tags = {
    Name = "${var.project_name}-public-subnet-1b"
  }
}

# --- Private App Subnets ---
# Used for EC2 instances. They can reach internet VIA Nat Gateway, but not directly.
resource "aws_subnet" "private_app_a" {
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.3.0/24"
  availability_zone = "ap-southeast-1a"

  tags = {
    Name = "${var.project_name}-private-app-subnet-1a"
  }
}

resource "aws_subnet" "private_app_b" {
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.4.0/24"
  availability_zone = "ap-southeast-1b"

  tags = {
    Name = "${var.project_name}-private-app-subnet-1b"
  }
}

# --- Private DB Subnets ---
# Used for RDS. No internet access allowed.
resource "aws_subnet" "private_db_a" {
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.5.0/24"
  availability_zone = "ap-southeast-1a"

  tags = {
    Name = "${var.project_name}-private-db-subnet-1a"
  }
}

resource "aws_subnet" "private_db_b" {
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.6.0/24"
  availability_zone = "ap-southeast-1b"

  tags = {
    Name = "${var.project_name}-private-db-subnet-1b"
  }
}

# --- NAT Gateways ---
# Allows private instances to initiate outbound traffic (e.g. download Docker images)
resource "aws_eip" "nat_a" {
  domain = "vpc"
}

resource "aws_eip" "nat_b" {
  domain = "vpc"
}

resource "aws_nat_gateway" "nat_a" {
  allocation_id = aws_eip.nat_a.id
  subnet_id     = aws_subnet.public_a.id # Resides in Public Subnet

  tags = {
    Name = "${var.project_name}-nat-1a"
  }
}

resource "aws_nat_gateway" "nat_b" {
  allocation_id = aws_eip.nat_b.id
  subnet_id     = aws_subnet.public_b.id

  tags = {
    Name = "${var.project_name}-nat-1b"
  }
}

# --- Route Tables ---
# Public Route Table -> Internet Gateway
resource "aws_route_table" "public" {
  vpc_id = aws_vpc.main.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.main.id
  }

  tags = {
    Name = "${var.project_name}-public-rt"
  }
}

resource "aws_route_table_association" "public_a" {
  subnet_id      = aws_subnet.public_a.id
  route_table_id = aws_route_table.public.id
}

resource "aws_route_table_association" "public_b" {
  subnet_id      = aws_subnet.public_b.id
  route_table_id = aws_route_table.public.id
}

# Private Route Tables -> NAT Gateway (specific to AZ)
resource "aws_route_table" "private_a" {
  vpc_id = aws_vpc.main.id

  route {
    cidr_block     = "0.0.0.0/0"
    nat_gateway_id = aws_nat_gateway.nat_a.id
  }

  tags = {
    Name = "${var.project_name}-private-rt-1a"
  }
}

resource "aws_route_table" "private_b" {
  vpc_id = aws_vpc.main.id

  route {
    cidr_block     = "0.0.0.0/0"
    nat_gateway_id = aws_nat_gateway.nat_b.id
  }

  tags = {
    Name = "${var.project_name}-private-rt-1b"
  }
}

resource "aws_route_table_association" "app_a" {
  subnet_id      = aws_subnet.private_app_a.id
  route_table_id = aws_route_table.private_a.id
}

resource "aws_route_table_association" "app_b" {
  subnet_id      = aws_subnet.private_app_b.id
  route_table_id = aws_route_table.private_b.id
}

resource "aws_route_table_association" "db_a" {
  subnet_id      = aws_subnet.private_db_a.id
  route_table_id = aws_route_table.private_a.id
}

resource "aws_route_table_association" "db_b" {
  subnet_id      = aws_subnet.private_db_b.id
  route_table_id = aws_route_table.private_b.id
}
