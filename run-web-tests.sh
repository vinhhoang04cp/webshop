#!/bin/bash

# Script để chạy Web Controller Tests
# Sử dụng: ./run-web-tests.sh [options]

# Colors cho output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   WebShop - Web Controllers Tests     ${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Function để chạy tests
run_tests() {
    local test_path=$1
    local test_name=$2
    
    echo -e "${YELLOW}Running ${test_name}...${NC}"
    ./vendor/bin/sail artisan test "$test_path" --compact
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ ${test_name} passed!${NC}"
    else
        echo -e "${RED}✗ ${test_name} failed!${NC}"
        exit 1
    fi
    echo ""
}

# Parse arguments
if [ "$1" == "--help" ] || [ "$1" == "-h" ]; then
    echo "Usage: ./run-web-tests.sh [option]"
    echo ""
    echo "Options:"
    echo "  (no option)    Run all Web tests"
    echo "  --auth         Run AuthController tests only"
    echo "  --home         Run HomeController tests only"
    echo "  --product      Run ProductController tests only"
    echo "  --cart         Run CartController tests only"
    echo "  --order        Run OrderController tests only"
    echo "  --category     Run CategoryController tests only"
    echo "  --profile      Run ProfileController tests only"
    echo "  --parallel     Run all tests in parallel"
    echo "  --coverage     Run tests with coverage report"
    echo "  --help, -h     Show this help message"
    exit 0
fi

# Clear cache before running tests
echo -e "${YELLOW}Clearing cache...${NC}"
./vendor/bin/sail artisan config:clear > /dev/null 2>&1
./vendor/bin/sail artisan cache:clear > /dev/null 2>&1
echo -e "${GREEN}✓ Cache cleared!${NC}"
echo ""

# Run specific test or all tests
case "$1" in
    --auth)
        run_tests "tests/Feature/Web/AuthControllerTest.php" "AuthController Tests"
        ;;
    --home)
        run_tests "tests/Feature/Web/HomeControllerTest.php" "HomeController Tests"
        ;;
    --product)
        run_tests "tests/Feature/Web/ProductControllerTest.php" "ProductController Tests"
        run_tests "tests/Feature/Web/CustomerProductControllerTest.php" "CustomerProductController Tests"
        ;;
    --cart)
        run_tests "tests/Feature/Web/CustomerCartControllerTest.php" "CustomerCartController Tests"
        ;;
    --order)
        run_tests "tests/Feature/Web/OrderControllerTest.php" "OrderController Tests"
        ;;
    --category)
        run_tests "tests/Feature/Web/CategoryControllerTest.php" "CategoryController Tests"
        ;;
    --profile)
        run_tests "tests/Feature/Web/ProfileControllerTest.php" "ProfileController Tests"
        ;;
    --parallel)
        echo -e "${YELLOW}Running all tests in parallel...${NC}"
        ./vendor/bin/sail artisan test tests/Feature/Web --parallel
        ;;
    --coverage)
        echo -e "${YELLOW}Running tests with coverage...${NC}"
        ./vendor/bin/sail artisan test tests/Feature/Web --coverage --min=70
        ;;
    *)
        echo -e "${YELLOW}Running all Web Controller tests...${NC}"
        echo ""
        
        run_tests "tests/Feature/Web/AuthControllerTest.php" "AuthController Tests"
        run_tests "tests/Feature/Web/HomeControllerTest.php" "HomeController Tests"
        run_tests "tests/Feature/Web/ProductControllerTest.php" "ProductController Tests"
        run_tests "tests/Feature/Web/CustomerProductControllerTest.php" "CustomerProductController Tests"
        run_tests "tests/Feature/Web/CustomerCartControllerTest.php" "CustomerCartController Tests"
        run_tests "tests/Feature/Web/OrderControllerTest.php" "OrderController Tests"
        run_tests "tests/Feature/Web/CategoryControllerTest.php" "CategoryController Tests"
        run_tests "tests/Feature/Web/ProfileControllerTest.php" "ProfileController Tests"
        
        echo -e "${GREEN}========================================${NC}"
        echo -e "${GREEN}   All tests passed successfully! ✓   ${NC}"
        echo -e "${GREEN}========================================${NC}"
        ;;
esac

exit 0

