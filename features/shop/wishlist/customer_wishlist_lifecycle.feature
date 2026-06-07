@wishlist @shop @ui @javascript
Feature: Guest and customer wishlist lifecycle
    In order to keep my saved products across visits and sign in
    As a shop customer
    I want guest and customer wishlists to stay visible in browser workflows

    Background:
        Given the store operates on a single channel in "United States"

    Scenario: A guest wishlist persists while browsing in the same browser
        Given the store has a product "Subaru Impreza WRX" priced at "$10.00"
        When I view product "Subaru Impreza WRX"
        And I add this product to my wishlist
        And I go back to the homepage
        Then my wishlist widget should show 1 item
        When I go to my wishlist page
        Then I should see product "Subaru Impreza WRX" in my wishlist

    Scenario: A guest wishlist is visible after signing in
        Given there is a customer account "tonysoprano@example.com"
        And the store has a product "Subaru Impreza WRX" priced at "$10.00"
        And my wishlist contains product "Subaru Impreza WRX"
        When I log in with the email "tonysoprano@example.com"
        And I go to my wishlist page
        Then I should see product "Subaru Impreza WRX" in my wishlist
        And my wishlist should contain 1 item
