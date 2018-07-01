# Assignment 1 for CSC309H5 - Programming on the Web.

This is a PHP website (no JS) built with with an MVC methodology.

The website is called "RestaurantMash".

Views:
  - **Compete**: Users are repeatedly given a pair of restaurants, and must vote on which is better. The restaurants are then ranked using an ELO system.
  - **Results**: After making 10 comparisons, users can see the global restaurant rankings, sorted by ELO rating.
  - **User Profile**: Users can add some info about themselves.
  - **Snake**: (Custom feature.) This is a game of snake with two pieces of food at once, where each piece of food represents a restaurant. Eating one of the two foods is a vote for that restaurant.
  - **Clickbait**: (Custom feature.) (Not important, just used for extra boosting.) Some random videos I found to inject some personality into my assignment.
  - **Logout**: Now that's what I call a self-documenting feature.

Other features:
  - Functionality:
    - Handles good and bad input.
    - Forms (e.g. user profile screen) re-fill current values.
    - Forms (e.g. login screen) re-fill on error.
    - Restaurant pairs are chosen by least-voted-on-restaurants first, and out of those, it's random.
    - Pagetokens on vote page to prevent voting twice.
    - POST forms to avoid sensitive data in URL.
    - Each user can only vote on a pair once.
    - Nav bar highlights current page.
  - DB:
    - Simple, effective schema.
    - Prepared statements to prevent SQL injection.
  - Model (PHP):
    - Model cached in session.
    - Great use of PHP.
  - View:
    - Frontend validation (whitelisting, required fields.)
    - Object IDs not accessible, preventing IDOR.
  - Controller:
    - Minimal data stored in session.
    - Backend validation (whitelisting, required fields.)
    - Error displaying.







---

Setup instructions:
  - TODO
