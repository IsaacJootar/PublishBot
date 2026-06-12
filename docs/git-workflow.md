# PublishAI — Git Workflow Rules

> Claude Code must read this file before making any git operations.
> These rules are non-negotiable and override any default git behaviour.

---

## Git identity — set this before the very first commit

```bash
git config user.name "Isaac Jootar"
git config user.email "jootarisaac@gmail.com"
```


Run this once inside the publishai folder before any commit.
Every commit must be authored by you — never leave git identity as default.

---

## Repository setup — first session only

```bash
git init
git add .
git commit -m "Initial commit — PublishAI spec files and project structure"
```

If using GitHub:
```bash
git remote add origin https://github.com/yourusername/publishai.git
git branch -M main
git push -u origin main
```

---

## Commit rules — follow exactly every phase

### When to commit
Commit ONCE per phase — when the phase is fully complete and tested.
Never commit partial work. Never commit untested code.

### Commit sequence (run in this exact order)
```bash
git add .
git commit -m "Phase [N] complete — [module name]: [what was built]"
git push
```

### Commit message format — mandatory

```
Phase [N] complete — [Module Name]: [specific features built]
```

Examples of correct commit messages:
```
Phase 01 complete — Project Setup: folder structure, SQLite init, Streamlit entry point
Phase 02 complete — Settings Page: API key storage, connection test, cost estimates
Phase 04 complete — My Voice: file upload, style extraction, voice profile save
Phase 05 complete — Research Module: 10 title angles, scoring, project creation
Phase 08 complete — Manuscript Writer: chapter generation, voice injection, word count
```

Examples of WRONG commit messages (never use these):
```
updates
fixed stuff
wip
module done
changes
```

---

## Phase completion report — required before every commit

Before committing, Claude Code must output this report:

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PHASE [N] COMPLETE — [Phase Name]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

FILES CREATED:
- [list every new file]

FILES MODIFIED:
- [list every modified file]

HOW TO TEST:
1. [exact step]
2. [exact step]
3. [exact step]

KNOWN ISSUES: [none / or describe any]

COMMITTING NOW...
[run git add, commit, push]

COMMIT HASH: [hash after commit]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
WAITING FOR APPROVAL TO START PHASE [N+1]
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

Then STOP. Do not write any more code. Wait.

---

## Approval protocol

After the phase completion report is shown:

**Claude Code must wait for one of these responses:**
- "approved"
- "go ahead"
- "continue"
- "next"
- "looks good"

**Claude Code must NOT proceed if it receives:**
- No response (silence)
- 👍 or any emoji alone
- "ok" without clear context
- Any unrelated question

If unclear whether approval was given, ask:
"Should I start Phase [N+1]? Please confirm."

---

## Branch strategy (optional — for safer building)

If you want to build safely with the ability to roll back:

```bash
# Start each phase on a new branch
git checkout -b phase-[N]-[name]

# After phase is complete and tested
git checkout main
git merge phase-[N]-[name]
git push

# Delete the phase branch
git branch -d phase-[N]-[name]
```

This is optional. Single branch on main is fine for solo building.

---

## .gitignore — must exist before first commit

Claude Code must create this file in the project root:

```
# Environment and secrets
.env
*.env

# Python
__pycache__/
*.py[cod]
*.pyc
.pytest_cache/
*.egg-info/
dist/
build/
.venv/
venv/
env/

# Database
data/publishai.db
data/publishai.db-shm
data/publishai.db-wal

# User data (never commit user content)
data/voice_profiles/
data/manuscripts/
data/exports/

# OS files
.DS_Store
Thumbs.db

# IDE
.vscode/
.idea/
*.swp
```

---

## What NEVER gets committed

- `.env` file (contains API keys — NEVER commit)
- Any file in `/data/` (user content — stays local)
- `__pycache__` folders
- Any file containing real API keys, tokens, or passwords

The `.gitignore` above handles all of these automatically.
