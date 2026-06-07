@wishlist @shop @ui @javascript
Feature: Managing wishlist contents
    In order to keep my wishlist useful
    As a shop visitor
    I want to remove wishlist items and move selected items to my cart

    Background:
        Given the store operates on a single channel in "United States"

    Scenario: Removing a product from my wishlist
        Given the store has a product "Audi Quattro" priced at "$10.00"
        And my wishlist contains product "Audi Quattro"
        When I go to my wishlist page
        And I remove product "Audi Quattro" from my wishlist
        Then my wishlist should contain 0 items
        And I should be notified that there are no wishlist items

    Scenario: Bulk deleting selected wishlist products
        Given the store has a product "Audi Quattro" priced at "$10.00"
        And the store has a product "Lancia Delta Integrale" priced at "$20.00"
        And the store has a product "Peugeot 205 GTI" priced at "$30.00"
        And my wishlist contains product "Audi Quattro"
        And my wishlist contains product "Lancia Delta Integrale"
        And my wishlist contains product "Peugeot 205 GTI"
        When I go to my wishlist page
        And I select product "Audi Quattro" in my wishlist
        And I select product "Lancia Delta Integrale" in my wishlist
        And I bulk delete selected wishlist products
        Then I should see product "Peugeot 205 GTI" in my wishlist
        And I should not see product "Audi Quattro" in my wishlist
        And I should not see product "Lancia Delta Integrale" in my wishlist
        And my wishlist should contain 1 item

    Scenario: Bulk deleting all wishlist products
        Given the store has a product "Audi Quattro" priced at "$10.00"
        And the store has a product "Lancia Delta Integrale" priced at "$20.00"
        And my wishlist contains product "Audi Quattro"
        And my wishlist contains product "Lancia Delta Integrale"
        When I go to my wishlist page
        And I select all wishlist products
        And I bulk delete selected wishlist products
        Then my wishlist should contain 0 items
        And I should be notified that there are no wishlist items

    Scenario: Adding selected wishlist products to my cart
        Given the store has a product "Audi Quattro" priced at "$10.00"
        And the store has a product "Lancia Delta Integrale" priced at "$20.00"
        And my wishlist contains product "Audi Quattro"
        And my wishlist contains product "Lancia Delta Integrale"
        When I go to my wishlist page
        And I select product "Audi Quattro" in my wishlist
        And I add selected wishlist products to my cart
        Then my cart should contain product "Audi Quattro"
        And my cart should not contain product "Lancia Delta Integrale"
