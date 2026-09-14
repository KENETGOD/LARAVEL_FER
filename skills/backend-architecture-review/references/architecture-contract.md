# Backend Architecture Contract

## Required request flow

Every representative backend request should follow:

`route → controller → service interface → service → repository interface → repository → database`

The response or error returns through the inverse path. The route selects the endpoint. The controller accepts and validates/parses transport input, invokes the service, and formats the HTTP response; it must not contain business logic. The service owns business rules, validation, and orchestration. The repository exclusively builds queries and maps persistence results. The database executes the query.

## Boundaries and dependency inversion

Service and repository layers each expose an interface contract. Higher layers depend on interfaces rather than concrete implementations. Laravel bindings must resolve those contracts to implementations. This permits replacing persistence implementations without changing services or controllers and permits unit tests to mock service and repository contracts.

## Error propagation

Errors must not be silently swallowed or handled inconsistently in the layer where they occur. Repository errors propagate to the service and then upward. The controller or centralized exception handler translates domain or infrastructure errors into the appropriate HTTP response (for example, 400, 404, or 500).

## Testability

Tests must demonstrate that service and repository dependencies can be mocked through their interfaces, allowing business logic tests without a real database. Evaluate implemented, exercised flows rather than empty classes, placeholders, or folder structure.
