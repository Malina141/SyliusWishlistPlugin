@wishlist @shop @ui @javascript
Feature: Viewing wishlist contents
    In order to come back to products I like
    As a shop visitor
    I want to see products, prices, options, and empty states saved in my wishlist

    Background:
        Given the store operates on a single channel in "United States"

    Scenario: Viewing an empty wishlist
        When I go to my wishlist page
        Then my wishlist should contain 0 items
        And I should be notified that there are no wishlist items

    Scenario: Viewing products in my wishlist
        Given the store has a product "Porsche 911 Turbo" priced at "$10.00"
        And my wishlist contains product "Porsche 911 Turbo"
        When I go to my wishlist page
        Then I should see product "Porsche 911 Turbo" in my wishlist
        And I should see "$10.00" as the price of product "Porsche 911 Turbo" in my wishlist
        And my wishlist should contain 1 item

    Scenario: Opening my wishlist from the header widget
        Given the store has a product "Porsche 911 Turbo" priced at "$10.00"
        And my wishlist contains product "Porsche 911 Turbo"
        When I follow the wishlist widget in the shop header
        Then I should be on my wishlist page
        And I should see product "Porsche 911 Turbo" in my wishlist
