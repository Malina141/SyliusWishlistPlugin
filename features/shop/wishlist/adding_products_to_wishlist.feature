@wishlist @shop @ui @javascript
Feature: Adding and toggling wishlist products while browsing
    In order to save products while browsing the shop
    As a shop visitor
    I want to add products to my wishlist from product detail and listing pages and see the controls stay in sync

    Background:
        Given the store operates on a single channel in "United States"

    Scenario: Adding a product to my wishlist from the product detail page
        Given the store has a product "Aston Martin DB5" priced at "$10.00"
        When I view product "Aston Martin DB5"
        And I add this product to my wishlist
        Then my wishlist widget should show 1 item
        And I should be able to remove this product from my wishlist
        When I go to my wishlist page
        Then I should see product "Aston Martin DB5" in my wishlist
        And my wishlist should contain 1 item

    Scenario: Removing a product from my wishlist on the product detail page
        Given the store has a product "Aston Martin DB5" priced at "$10.00"
        And my wishlist contains product "Aston Martin DB5"
        When I view product "Aston Martin DB5"
        And I remove this product from my wishlist
        Then my wishlist widget should show 0 items
        When I go to my wishlist page
        Then my wishlist should contain 0 items
        And I should be notified that there are no wishlist items

    Scenario: Adding a product to my wishlist from the product list page
        Given the store classifies its products as "Cars"
        And the store has a product "Aston Martin DB5" priced at "$10.00" belonging to the "Cars" taxon
        When I browse products from taxon "Cars"
        And I add product "Aston Martin DB5" to my wishlist from the product list
        Then my wishlist widget should show 1 item
        When I go to my wishlist page
        Then I should see product "Aston Martin DB5" in my wishlist
        And my wishlist should contain 1 item

    Scenario: Removing a product from my wishlist on the product list page
        Given the store classifies its products as "Cars"
        And the store has a product "Aston Martin DB5" priced at "$10.00" belonging to the "Cars" taxon
        And my wishlist contains product "Aston Martin DB5"
        When I browse products from taxon "Cars"
        And I remove product "Aston Martin DB5" from my wishlist from the product list
        Then my wishlist widget should show 0 items
        When I go to my wishlist page
        Then my wishlist should contain 0 items
        And I should be notified that there are no wishlist items
