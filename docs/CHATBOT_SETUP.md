# Google Gemini AI Chatbot - Setup Guide

## Quick Start

Your SmartShopper now has an AI assistant powered by Google Gemini! Here's how to enable it:

## Step 1: Get Your Google API Key

1. Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Click "Create API Key"
3. Copy the API key

## Step 2: Add to Environment

Add this to your `.env` file:

```
GEMINI_API_KEY=your_api_key_here
```

Replace `your_api_key_here` with your actual API key.

## Step 3: Test the Chatbot

1. Login to your SmartShopper
2. Click "AI Chat" in the navigation menu
3. Start chatting with the AI assistant!

## Features

✅ **Shopping Assistance** - Get product recommendations
✅ **Price Comparisons** - Ask about prices across retailers
✅ **Budget Planning** - Get budget advice
✅ **Conversation History** - Chat history is saved per user
✅ **Dark Mode Support** - Works in light and dark themes

## API Pricing

- **Free Tier**: 60 requests per minute
- **Cost**: Free for testing, then ~$0.00025 per token after free quota

## Troubleshooting

### "Invalid response from AI"
- Check your API key is correct in `.env`
- Make sure you have internet connection
- Try a simpler question first

### Chat not loading
- Clear browser cache
- Check browser console for errors
- Ensure you're logged in

### Rate limited
- You've hit the free tier limit
- Wait a bit before sending more messages
- Consider upgrading your Google project

## Customization

Want to change how the AI behaves? Edit `app/Http/Controllers/ChatController.php`:

- **Line 160-179**: Modify `buildSystemPrompt()` to change personality
- **Lines 94-98**: Adjust `generationConfig` for different response styles
  - `temperature`: 0.7 (higher = more creative, lower = more factual)
  - `maxOutputTokens`: 500 (max response length)

Enjoy your AI chatbot! 🤖
