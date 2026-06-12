# PublishAI — Claude Code Setup Guide
# RTK + Super Memory + Caveman

> Read this file once before your first build session.
> After setup, these three tools run silently in the background forever.
> You will never need to think about them again.

---

## Why these tools matter for this project

PublishAI has 17 build phases, 28 spec files, and a pipeline that touches
SQLite, Python modules, Streamlit pages, and the Claude API on every session.

Without optimisation, Claude Code will:
- Fill its context window with terminal noise (pip install output, test
  results, file reads) and lose the thread of what it was building
- Forget everything between sessions — your architecture decisions,
  your phase progress, your naming conventions
- Write long explanatory responses that eat context budget unnecessarily

RTK, Super Memory, and Caveman solve each of these problems respectively.

---

## Tool 1 — RTK (Rust Token Killer)
### What it does: kills terminal noise BEFORE it enters context (60-90% reduction)

Every time Claude Code runs a bash command during the build — pip install,
python app.py, pytest, sqlite3 queries — the raw output floods the context
window. RTK intercepts that output and strips the noise before Claude sees it.

### Install RTK (run these in your terminal — one time only)

**Mac / Linux:**
```bash
curl -sSL https://rtk-ai.app/install.sh | sh
```

**Windows (use WSL or run this in WSL terminal):**
```bash
curl -sSL https://rtk-ai.app/install.sh | sh
```

**Windows native (if not using WSL):**
RTK installs a CLAUDE.md fallback mode instead of the hook.
Run: `rtk init --claude-md` from inside the publishai folder.

### Connect RTK to Claude Code (one time only)

```bash
rtk init -g
```

This installs a PreToolUse hook in Claude Code's settings.json automatically.
Restart Claude Code after running this. That's it — RTK runs silently forever.

### Verify it's working

After your first build session, run:
```bash
rtk gain
```
It shows you exactly how many tokens were saved. Expect 60-90% on pip and
test output, 40-60% on file reads.

### What RTK compresses in this project

| Command | Raw tokens | After RTK |
|---|---|---|
| pip install -r requirements.txt | ~3,000 | ~300 |
| streamlit run app.py (startup) | ~1,500 | ~150 |
| python -m pytest | ~5,000 | ~500 |
| sqlite3 database queries | ~800 | ~100 |
| git status / git diff | ~600 | ~80 |

### Important note for this project

If a command FAILS, RTK saves the full unfiltered output to disk so
Claude Code can read the complete error. You never lose error details.

---

## Tool 2 — Super Memory (claude-supermemory)
### What it does: remembers everything between sessions

Claude Code forgets everything when a session ends. Super Memory captures
your important decisions, architecture choices, and progress — then injects
them back at the start of every new session automatically.

For PublishAI this means: Claude Code always knows which phase you're on,
what decisions were made on the database schema, how the voice profile
system works, and what was built in previous sessions.

### Install Super Memory

```bash
npm install -g @supermemoryai/claude-supermemory
```

Then add it as an MCP server in Claude Code. In your terminal inside the
publishai folder:

```bash
claude mcp add supermemory -- supermemory-claude
```

Or add manually to your Claude Code settings.json:
```json
{
  "mcpServers": {
    "supermemory": {
      "command": "supermemory-claude",
      "args": []
    }
  }
}
```

### Get your Super Memory API key

Sign up free at supermemory.ai
Copy your API key and run the setup command:
```bash
/claude-supermemory:setup
```
Paste your API key when prompted.

### Configure Super Memory for PublishAI

Create this file: `~/.supermemory-claude/settings.json`

```json
{
  "maxProfileItems": 8,
  "signalExtraction": true,
  "signalKeywords": [
    "voice_profile",
    "pipeline",
    "phase",
    "schema",
    "module",
    "architecture",
    "decision",
    "bug",
    "fix",
    "completed",
    "CLAUDE.md",
    "publishai"
  ],
  "signalTurnsBefore": 3,
  "includeTools": ["Edit", "Write", "MultiEdit"]
}
```

Create a project config inside publishai folder:
`.claude/.supermemory-claude/config.json`

```json
{
  "repoContainerTag": "publishai-project",
  "signalExtraction": true
}
```

### What Super Memory captures for this project

Every time Claude Code makes a significant decision or completes a phase,
Super Memory captures it. At the start of the next session, it injects:

- Which phase was completed last
- Any schema changes made
- Architecture decisions (why certain approaches were chosen)
- Bugs that were found and fixed
- Module completion status
- Voice profile system decisions

This means you never re-explain the project. You open Claude Code, it
reads CLAUDE.md AND its memory, and it knows exactly where it left off.

### How to manually trigger a memory save

At any point in a session, say:
```
remember: [whatever you want saved]
```
Examples:
```
remember: phase 05 research module is complete and tested
remember: voice profile uses extracted_style field from voice_profiles table
remember: streamlit pages use st.session_state for pipeline step tracking
remember: decided to use JSON strings for keyword storage in kdp_listings
```

---

## Tool 3 — Caveman
### What it does: makes Claude Code responses 65-75% shorter without losing accuracy

Claude Code's explanations and commentary become context on the next turn.
Long responses = less context budget for actual building. Caveman strips
all filler, pleasantries, and over-explanation from responses while keeping
code, errors, and technical decisions completely intact.

Research shows shorter constrained responses are also MORE accurate on
mechanical coding tasks — which is exactly what 17 phases of building is.

### Install Caveman

```bash
claude skills install caveman
```

Or install via the Claude Code plugin hub:
```bash
/install JuliusBrussee/caveman
```

### Activate Caveman for a session

At the start of any build session, type:
```
/caveman
```

Caveman activates for the session. Claude Code responses immediately become
dense and direct. No pleasantries, no repetition, no "Great question!" filler.

For maximum token savings during long build sessions:
```
/caveman ultra
```

### When to use Caveman vs when to turn it off

**USE Caveman for:**
- Building phases (writing code, creating files, running tests)
- Debugging sessions
- Any mechanical task in phases 01-17

**TURN OFF Caveman for:**
- Architecture discussions where you want full explanations
- When something goes wrong and you need Claude to explain its reasoning
- First session on a new phase where you want to understand the approach

To turn off:
```
/caveman off
```

### What Caveman compression looks like

**Without Caveman:**
"Great question! The reason your Streamlit session state isn't persisting
is likely because of how Streamlit handles reruns. When a user interacts
with a widget, Streamlit reruns the entire script from top to bottom. This
means any variables defined outside of session_state get reset. To fix this,
you'll want to initialise your variables inside st.session_state like this..."

**With Caveman:**
"session_state not init'd. Add: if 'step' not in st.session_state:
st.session_state.step = 1 at top of page."

Same fix. 75% fewer tokens. Context window lasts 3x longer.

---

## Session Start Ritual — do this every session

When you open Claude Code in the publishai folder, start every session
with this exact message:

```
Read CLAUDE.md. Check your memory for this project. Tell me:
1. What phase are we on?
2. What was completed last session?
3. What should we build next?
Then activate /caveman and let's build.
```

Claude Code will:
1. Read your full spec from CLAUDE.md
2. Pull in Super Memory context from last session
3. Tell you exactly where you are
4. Activate Caveman for the session
5. Be ready to build immediately

---

## Combined effect on this project

| Problem | Solution | Result |
|---|---|---|
| Terminal noise fills context | RTK | 60-90% less input noise |
| Forgets progress between sessions | Super Memory | Always knows where it left off |
| Long responses eat context budget | Caveman | 65-75% shorter outputs |
| **Combined effect** | **All three** | **Sessions last 3-5x longer** |

For a 17-phase build like PublishAI, this is the difference between
finishing in days versus running out of context budget constantly and
having to restart and re-explain everything repeatedly.

---

## Troubleshooting

**RTK not intercepting commands:**
Run `rtk --version` to confirm it's installed.
Run `rtk init -g` again and restart Claude Code.

**Super Memory not injecting at session start:**
Check that the MCP server is listed in Claude Code settings.json.
Run `/claude-supermemory:status` to check connection.

**Caveman making responses too terse on a complex task:**
Run `/caveman off` for that task, then `/caveman` again after.

**All three tools together causing unexpected behaviour:**
These tools are independent. Disable them one at a time to isolate.
RTK: `rtk off` | Caveman: `/caveman off` | Super Memory: remove from MCP.
