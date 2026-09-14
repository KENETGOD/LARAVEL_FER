---
name: backend-architecture-review
description: "Trigger: backend architecture review, Laravel architecture audit, architecture contract check. Review Laravel layering against the local architecture contract."
license: Apache-2.0
metadata:
  author: "project-local"
  version: "1.0"
---

## Activation Contract

Activate when asked to review this Laravel backend against its architecture contract or layered backend flow.

## Hard Rules

- Read `references/architecture-contract.md` before reviewing.
- Inspect routes, controllers, service interfaces, services, repositories, repository interfaces, container bindings, exception handling, and tests.
- Trace real request flows; distinguish placeholders, stubs, and unused scaffolding from implemented business flows.
- Never infer compliance from folder names, class names, or declared interfaces alone.
- Cite exact repository-relative paths and line ranges for every finding.

## Decision Gates

| Criterion | Classify |
|---|---|
| Required flow and responsibility are evidenced by an exercised flow | Compliant |
| Evidence is incomplete, mixed, or only partially implemented | Partial |
| The flow, responsibility, dependency inversion, error propagation, or testability rule is violated | Non-compliant |

Treat absent evidence as partial when the code path is not exercised; treat a clear violation as non-compliant.

## Execution Steps

1. Map representative routes through controller, service contract, service, repository contract, repository, and database access.
2. Check controller boundaries, repository-only data access, interface usage, Laravel bindings, exception propagation, and mockable tests.
3. Record criterion, classification, evidence, and exact line ranges; separate placeholders from real flows.
4. Rank deviations by severity and derive an overall verdict from the highest-impact findings.

## Output Contract

Return: scope and flows reviewed; a criterion table with `compliant`, `partial`, or `non-compliant`; severity-ranked deviations with path/line evidence; missing or placeholder coverage; and one overall verdict.

## References

- `references/architecture-contract.md`
