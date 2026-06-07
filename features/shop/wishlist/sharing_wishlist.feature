@wishlist @shop @ui @javascript
Feature: Sharing a wishlist
    In order to show my saved products to someone else
    As a shop visitor
    I want to share and unshare my wishlist using a public link

    Background:
        Given the store operates on a single channel in "United States"

    Scenario: Sharing my wishlist creates a public link
        Given the store has a product "Passerati" priced at "$10.00"
        And my wishlist contains product "Passerati"
        When I go to my wishlist page
        And I share my wishlist
        Then I should be notified that the wishlist has been successfully updated
        And my wishlist should be marked as shared
        And I should see a public wishlist link
        And I should be able to copy the public wishlist link

    Scenario: Viewing a shared wishlist by public link
        Given the store has a product "Passerati" priced at "$10.00"
        And my wishlist contains product "Passerati"
        And my wishlist is shared
        When another visitor opens my public wishlist link
        Then they should see a shared wishlist page
        And they should see product "Passerati" in the shared wishlist
        And they should see "$10.00" as the price of product "Passerati" in the shared wishlist

    Scenario: Unsharing my wishlist invalidates the public link
        Given the store has a product "Passerati" priced at "$10.00"
        And my wishlist contains product "Passerati"
        And my wishlist is shared
        When I go to my wishlist page
        And I unshare my wishlist
        Then I should be notified that the wishlist has been successfully updated
        And my wishlist should be marked as private
        And I should not see a public wishlist link
        When another visitor opens the previously public wishlist link
        Then they should not be able to see my shared wishlist
