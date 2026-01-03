#!/bin/bash
# Clean WhatsApp Sessions Script

SESSIONS_DIR="./sessions"

echo "🧹 Cleaning WhatsApp Sessions..."
echo "Sessions directory: $SESSIONS_DIR"

# Count sessions before cleanup
BEFORE=$(find "$SESSIONS_DIR" -maxdepth 1 -type d ! -name "$SESSIONS_DIR" ! -name "." ! -name ".." | wc -l)

# Remove all session folders except .gitkeep
find "$SESSIONS_DIR" -maxdepth 1 -type d ! -name "$SESSIONS_DIR" ! -name "." ! -name ".." -exec rm -rf {} +

# Count sessions after cleanup
AFTER=$(find "$SESSIONS_DIR" -maxdepth 1 -type d ! -name "$SESSIONS_DIR" ! -name "." ! -name ".." | wc -l)

echo "✅ Cleanup complete!"
echo "   Removed sessions: $BEFORE"
echo "   Sessions remaining: $AFTER"
echo ""
echo "Restart the server to generate fresh QR codes:"
echo "   npm start"
