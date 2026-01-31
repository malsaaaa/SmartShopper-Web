#!/bin/bash
# SmartShopper Web Scraping - Deployment Verification Script
# Run this to verify everything is set up correctly

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║  SmartShopper Web Scraping - Deployment Verification     ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check function
check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $2"
        return 0
    else
        echo -e "${RED}✗${NC} $2 (Missing: $1)"
        return 1
    fi
}

check_command() {
    if command -v "$1" &> /dev/null; then
        echo -e "${GREEN}✓${NC} $2"
        return 0
    else
        echo -e "${RED}✗${NC} $2 (Not found: $1)"
        return 1
    fi
}

# Counters
total=0
passed=0

echo "📋 CHECKING FILE STRUCTURE..."
echo ""

# Check Python files
check_file "scrapers/scrape_products.py" "Main scraper script" && ((passed++))
((total++))

check_file "scrapers/config.py" "Configuration file" && ((passed++))
((total++))

check_file "scrapers/manage.py" "Management CLI tool" && ((passed++))
((total++))

check_file "scrapers/test_scraper.py" "Test suite" && ((passed++))
((total++))

check_file "scrapers/README.md" "Scraper documentation" && ((passed++))
((total++))

# Check PHP files
check_file "app/Console/Commands/ImportPrices.php" "Laravel command" && ((passed++))
((total++))

# Check documentation
echo ""
echo "📚 CHECKING DOCUMENTATION..."
echo ""

check_file "QUICK_START_SCRAPING.md" "Quick start guide" && ((passed++))
((total++))

check_file "SCRAPING_IMPLEMENTATION.md" "Technical documentation" && ((passed++))
((total++))

check_file "IMPLEMENTATION_SUMMARY.md" "Implementation overview" && ((passed++))
((total++))

check_file "TROUBLESHOOTING.md" "Troubleshooting guide" && ((passed++))
((total++))

check_file "VALIDATION_CHECKLIST.md" "Validation checklist" && ((passed++))
((total++))

check_file "COMPLETE_IMPLEMENTATION.md" "Completion summary" && ((passed++))
((total++))

check_file "SCRAPING_README.md" "Documentation index" && ((passed++))
((total++))

# Check commands
echo ""
echo "🔧 CHECKING SYSTEM REQUIREMENTS..."
echo ""

check_command "python3" "Python 3 installed" && ((passed++))
((total++))

check_command "php" "PHP installed" && ((passed++))
((total++))

# Summary
echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║                    VERIFICATION RESULTS                   ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
echo "Checks Passed: $passed / $total"
echo ""

if [ $passed -eq $total ]; then
    echo -e "${GREEN}✓ All checks passed! System is ready to deploy.${NC}"
    echo ""
    echo "📖 Next Steps:"
    echo "1. Read: QUICK_START_SCRAPING.md"
    echo "2. Run: python3 scrapers/test_scraper.py"
    echo "3. Test: php artisan prices:import"
    echo "4. Check: Dashboard for prices"
    exit 0
else
    echo -e "${YELLOW}⚠ Some checks failed. Review the items above.${NC}"
    echo ""
    echo "📖 Installation Help:"
    echo "1. Install missing files by re-running setup"
    echo "2. Install Python dependencies: pip install selenium beautifulsoup4 requests webdriver-manager"
    echo "3. Run: php artisan migrate (if needed)"
    exit 1
fi
