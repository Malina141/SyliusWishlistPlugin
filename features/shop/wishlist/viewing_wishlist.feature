@wishlist @shop @ui
Feature: Viewing wishlist contents
    In order to come back to products I like
    As a shop visitor
    I want to see products saved in my wishlist

    Background:
        Given the store operates on a single channel in "United States"

    Scenario: Viewing products in my wishlist
        Given the store has a product "Volvo S80 V8" priced at "$10.00"
        And my wishlist contains product "Volvo S80 V8"
        When I go to my wishlist page
        Then I should see product "Volvo S80 V8" in my wishlist
        And I should see "$10.00" as the price of product "Volvo S80 V8" in my wishlist
        And my wishlist should contain 1 item

    Scenario: Viewing multiple products in my wishlist
        Given the store has a product "Volvo S80 V8" priced at "$10.00"
        And the store has a product "Toyota Celica" priced at "$20.00"
        And my wishlist contains product "Volvo S80 V8"
        And my wishlist contains product "Toyota Celica"
        When I go to my wishlist page
        Then I should see product "Volvo S80 V8" in my wishlist
        And I should see product "Toyota Celica" in my wishlist
        And my wishlist should contain 2 items

    Scenario: Removing a product from my wishlist
        Given the store has a product "Volvo S80 V8" priced at "$10.00"
        And my wishlist contains product "Volvo S80 V8"
        When I go to my wishlist page
        And I remove product "Volvo S80 V8" from my wishlist
        Then my wishlist should contain 0 items
        And I should be notified that there are no wishlist items

    Scenario: Removing one product from a wishlist with multiple products
        Given the store has a product "Volvo S80 V8" priced at "$10.00"
        And the store has a product "Toyota Celica" priced at "$20.00"
        And my wishlist contains product "Volvo S80 V8"
        And my wishlist contains product "Toyota Celica"
        When I go to my wishlist page
        And I remove product "Volvo S80 V8" from my wishlist
        Then I should see product "Toyota Celica" in my wishlist
        And my wishlist should contain 1 item

    Scenario: Viewing an empty wishlist
        When I go to my wishlist page
        Then my wishlist should contain 0 items
        And I should be notified that there are no wishlist items
