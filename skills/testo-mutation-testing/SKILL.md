---
name: testo-mutation-testing
description: Run mutation testing on a Testo project with Infection (via testo/bridge-infection), collect surviving mutants efficiently, and kill them by strengthening tests. Use when the user asks about "mutation testing", "infection", "MSI", "mutation score", "escaped mutants", "kill mutants", or wants to measure how good the tests really are.
---

# Mutation testing with Testo + Infection

Four phases: **set up** the scratch dir, **generate coverage**, **collect** surviving mutants, **kill** them. Run every command from the project root (never `cd` into `vendor/bin`).

Related skills: `testo-coverage` (coverage setup), `testo-write-tests` (assertions). Fetch
`https://php-testo.github.io/llms.txt` before editing tests.

## Phase 1 — Choose the scratch dir

Resolve `<tmpDir>` once; use it in every command in later phases.

1. Open `infection.json`. If it has a `tmpDir` key, `<tmpDir>` = that value.
2. Otherwise `<tmpDir>` = `runtime` if that directory exists, else `build`.

`<tmpDir>` is also passed as the process temp dir (`-d sys_temp_dir`), so it must exist: `mkdir -p <tmpDir>` if missing. Its `cov`/`mut` subdirectories are created by Testo and Infection themselves.

## Phase 2 — Generate coverage

### 1. Run once

```bash
php -d xdebug.mode=coverage -d xdebug.max_nesting_level=100000 -d sys_temp_dir=<tmpDir> \
  vendor/bin/testo --json \
    --coverage-xml=<tmpDir>/cov/coverage-xml \
    --log-junit=<tmpDir>/cov/junit.xml
```

### 2. Gate — check it passed

If the command exits non-zero (failing tests), YOU MUST STOP: report the failing tests to the user and do not continue.

## Phase 3 — Collect

### 1. Run Infection per module (reuse coverage)

```bash
php -d memory_limit=4G -d sys_temp_dir=<tmpDir> vendor/bin/infection \
  --configuration=infection.json --quiet \
  --coverage=<tmpDir>/cov --skip-initial-tests \           # <tmpDir>/cov = coverage dir from Phase 2
  --filter=plugin/payments \                              # <- source dir of the module under test
  --threads=max --log-verbosity=default \
  --logger-summary-json=<tmpDir>/mut/payments.json \       # <- name output files per module
  --logger-gitlab=<tmpDir>/mut/payments.gitlab.json \
  --no-progress
```

- One run per module via `--filter`; never a single whole-project run. Loop over every source directory in `infection.json`.
- Loggers: `--logger-summary-json` + `--logger-gitlab` only. NEVER `--logger-text` / `--log-verbosity=all` — it OOMs. Keep `--log-verbosity=default` (`none` disables file loggers).
- Skip benchmark/recursive modules (e.g. `plugin/bench`).

### 2. Build the report

Read all `<tmpDir>/mut/*.json`, write `<tmpDir>/mutation-report.md`:
- Table: module → mutants, killed, escaped, errors, timeout, MSI. Sort by MSI ascending. Add a project-total row (`MSI = (killed + errors + timeouts) / total`).
- Per module, list escaped mutants from `*.gitlab.json` as `file:line (mutator)`.

## Phase 4 — Kill

Work-list = `<tmpDir>/mut/<module>.gitlab.json`. Each entry: `check_name`, `location.path`, `location.lines.begin`, `content` (diff).

For each escaped mutant:
1. Read the diff. Identify the broken behaviour.
2. Add or strengthen a test assertion that FAILS on the mutated code (see kill table).
3. Re-run that one module (Phase 3 step 1). Confirm escaped count dropped.

Skip equivalent mutants (no observable effect — e.g. formatting, dead code). Note them as accepted; do not contort tests.

### Kill table

| Mutator | Change | Kill it by |
|---|---|---|
| `ReturnRemoval` / `ReturnValue` | drops/alters `return` | Assert the returned value. |
| `AssignCoalesce` / `Coalesce` | drops `??` / `??=` guard | Test the already-set path; assert no overwrite. |
| `TrueValue` / `FalseValue` / negation mutators | flips bool/condition | Exercise the opposite branch; assert differing outcome. |
| `IncrementInteger` / `DecrementInteger` / `Plus` / `Minus` | `n` → `n±1` | Use boundary inputs `n-1, n, n+1`. |
| `MethodCallRemoval` / `FunctionCallRemoval` | drops a call | Assert the observable side effect. |
| `LogicalAnd` / `LogicalOr` | swaps AND ↔ OR | Inputs where only one operator gives the expected result. |
| `MatchArmRemoval` / `SharedCaseRemoval` | drops a `match`/`case` arm | One case per arm asserting its distinct result. |
| `Identical` / `NotIdentical` | `===` ↔ `!==` | Inputs distinguishing the two. |

Boundary/branch survivors are usually a missing `#[DataSet]` row — add the case, not a new method (see `testo-data-driven`).

### Re-coverage

Regenerate coverage (Phase 2 step 1) after editing **source**. Adding tests only: reuse existing coverage unless mutants report "not covered".

## Reference

- `composer infect` — CI gate. Sets `XDEBUG_MODE=coverage`, `TESTO_CI=1`, runs whole project with `--min-msi`.
- `FinderException: testo executable not found` on a direct `vendor/bin/infection` call → `export PATH="$PWD/vendor/bin:$PATH"`.
- `-d sys_temp_dir=<tmpDir>` keeps Symfony process temp files in a real dir. Required when the shell sets `TMP`/`TEMP` to a missing path (editor terminals like Zed → `WindowsPipes ... temporary file could not be opened`); harmless otherwise.
- `--threads=max` may run single-threaded on some platforms; the speedup is `--skip-initial-tests`, not threads.
- Deep recursion fixtures trip `xdebug.max_nesting_level`; `-d xdebug.mode=coverage` disables the loop detector, or raise the limit, or exclude the module.
