#!/usr/bin/env python3
"""
ASPIRE AI Bridge - Multi-Model Orchestration

Provides a unified interface for calling multiple AI providers
(Gemini, OpenAI, Claude, Ollama) with per-task model routing,
fallback chains, and batch processing.

Usage from PHP:
    python ai_bridge.py --action generate --provider gemini --model gemini-3.6-flash --prompt "..."
    python ai_bridge.py --action generate_json --provider openai --model gpt-4o --prompt "..."
    python ai_bridge.py --action batch --config batch_config.json
    python ai_bridge.py --action status

Requires: pip install google-generativeai openai anthropic requests
"""

import argparse
import json
import os
import sys
import time
import traceback
from typing import Optional

try:
    import google.generativeai as genai
except ImportError:
    genai = None

try:
    import openai
except ImportError:
    openai = None

try:
    import anthropic
except ImportError:
    anthropic = None

try:
    import requests
except ImportError:
    requests = None


def generate_gemini(model: str, prompt: str, temperature: float = 0.5,
                    max_tokens: int = 1024, api_key: str = "") -> dict:
    """Generate text using Google Gemini API."""
    if not genai:
        return {"success": False, "error": "google-generativeai package not installed. Run: pip install google-generativeai"}

    api_key = api_key or os.getenv("GEMINI_API_KEY", "")
    if not api_key:
        return {"success": False, "error": "GEMINI_API_KEY not configured"}

    try:
        genai.configure(api_key=api_key)
        model_instance = genai.GenerativeModel(model)
        response = model_instance.generate_content(
            prompt,
            generation_config=genai.types.GenerationConfig(
                temperature=temperature,
                max_output_tokens=max_tokens,
            )
        )

        text = response.text if response.text else ""
        tokens_used = 0
        if hasattr(response, "usage_metadata") and response.usage_metadata:
            tokens_used = getattr(response.usage_metadata, "total_token_count", 0)

        return {
            "success": True,
            "text": text.strip(),
            "model": model,
            "provider": "gemini",
            "tokens": tokens_used,
        }
    except Exception as e:
        return {"success": False, "error": str(e), "provider": "gemini"}


def generate_openai(model: str, prompt: str, temperature: float = 0.5,
                    max_tokens: int = 1024, api_key: str = "") -> dict:
    """Generate text using OpenAI API."""
    if not openai:
        return {"success": False, "error": "openai package not installed. Run: pip install openai"}

    api_key = api_key or os.getenv("OPENAI_API_KEY", "")
    if not api_key:
        return {"success": False, "error": "OPENAI_API_KEY not configured"}

    try:
        client = openai.OpenAI(api_key=api_key)
        response = client.chat.completions.create(
            model=model,
            messages=[{"role": "user", "content": prompt}],
            temperature=temperature,
            max_tokens=max_tokens,
        )

        text = response.choices[0].message.content or ""
        tokens_used = 0
        if response.usage:
            tokens_used = response.usage.total_tokens

        return {
            "success": True,
            "text": text.strip(),
            "model": model,
            "provider": "openai",
            "tokens": tokens_used,
        }
    except Exception as e:
        return {"success": False, "error": str(e), "provider": "openai"}


def generate_claude(model: str, prompt: str, temperature: float = 0.5,
                    max_tokens: int = 1024, api_key: str = "") -> dict:
    """Generate text using Anthropic Claude API."""
    if not anthropic:
        return {"success": False, "error": "anthropic package not installed. Run: pip install anthropic"}

    api_key = api_key or os.getenv("CLAUDE_API_KEY", "")
    if not api_key:
        return {"success": False, "error": "CLAUDE_API_KEY not configured"}

    try:
        client = anthropic.Anthropic(api_key=api_key)
        response = client.messages.create(
            model=model,
            max_tokens=max_tokens,
            temperature=temperature,
            messages=[{"role": "user", "content": prompt}],
        )

        text = response.content[0].text if response.content else ""
        tokens_used = 0
        if hasattr(response, "usage") and response.usage:
            tokens_used = (response.usage.input_tokens or 0) + (response.usage.output_tokens or 0)

        return {
            "success": True,
            "text": text.strip(),
            "model": model,
            "provider": "claude",
            "tokens": tokens_used,
        }
    except Exception as e:
        return {"success": False, "error": str(e), "provider": "claude"}


def generate_ollama(model: str, prompt: str, temperature: float = 0.5,
                    max_tokens: int = 1024, base_url: str = "") -> dict:
    """Generate text using local Ollama API."""
    if not requests:
        return {"success": False, "error": "requests package not installed. Run: pip install requests"}

    base_url = base_url or os.getenv("OLLAMA_URL", "http://localhost:11434")

    try:
        response = requests.post(
            f"{base_url}/api/generate",
            json={
                "model": model,
                "prompt": prompt,
                "stream": False,
                "options": {
                    "temperature": temperature,
                    "num_predict": max_tokens,
                },
            },
            timeout=60,
        )
        response.raise_for_status()
        data = response.json()

        return {
            "success": True,
            "text": data.get("response", "").strip(),
            "model": model,
            "provider": "ollama",
            "tokens": data.get("eval_count", 0),
        }
    except Exception as e:
        return {"success": False, "error": str(e), "provider": "ollama"}


def generate(provider: str, model: str, prompt: str, **kwargs) -> dict:
    """Route generation to the appropriate provider."""
    generators = {
        "gemini": lambda: generate_gemini(model, prompt, **kwargs),
        "openai": lambda: generate_openai(model, prompt, **kwargs),
        "claude": lambda: generate_claude(model, prompt, **kwargs),
        "ollama": lambda: generate_ollama(model, prompt, **kwargs),
    }

    gen_fn = generators.get(provider)
    if not gen_fn:
        return {"success": False, "error": f"Unknown provider: {provider}"}

    start = time.time()
    result = gen_fn()
    result["response_time_ms"] = round((time.time() - start) * 1000)

    return result


def generate_with_fallback(fallback_chain: list, prompt: str, **kwargs) -> dict:
    """
    Try multiple providers in order, returning the first successful result.

    fallback_chain: [{"provider": "openai", "model": "gpt-4o"}, {"provider": "gemini", "model": "gemini-3.6-flash"}]
    """
    for entry in fallback_chain:
        result = generate(
            provider=entry["provider"],
            model=entry["model"],
            prompt=prompt,
            **{k: v for k, v in entry.items() if k not in ("provider", "model")},
            **kwargs,
        )
        if result.get("success"):
            return result

    return {"success": False, "error": "All providers in fallback chain failed"}


def batch_process(config: dict) -> list:
    """
    Process multiple prompts with per-item provider/model routing.

    config: {
        "items": [
            {"id": "1", "provider": "gemini", "model": "gemini-3.6-flash", "prompt": "..."},
            {"id": "2", "provider": "openai", "model": "gpt-4o", "prompt": "..."},
        ],
        "fallback_chain": [{"provider": "gemini", "model": "gemini-3.6-flash"}]
    }
    """
    items = config.get("items", [])
    fallback = config.get("fallback_chain", [])
    results = []

    for item in items:
        item_id = item.get("id", str(len(results)))
        provider = item.get("provider", "")
        model = item.get("model", "")
        prompt = item.get("prompt", "")
        options = {k: v for k, v in item.items() if k not in ("id", "provider", "model", "prompt")}

        if provider and model:
            result = generate(provider, model, prompt, **options)
        elif fallback:
            result = generate_with_fallback(fallback, prompt, **options)
        else:
            result = {"success": False, "error": "No provider specified and no fallback chain"}

        result["id"] = item_id
        results.append(result)

    return results


def check_provider_status() -> dict:
    """Check which providers are configured and reachable."""
    status = {}

    # Gemini
    gemini_key = os.getenv("GEMINI_API_KEY", "")
    status["gemini"] = {
        "configured": bool(gemini_key),
        "model": os.getenv("GEMINI_MODEL", "gemini-3.6-flash"),
    }

    # OpenAI
    openai_key = os.getenv("OPENAI_API_KEY", "")
    status["openai"] = {
        "configured": bool(openai_key),
        "model": os.getenv("OPENAI_MODEL", "gpt-4o"),
    }

    # Claude
    claude_key = os.getenv("CLAUDE_API_KEY", "")
    status["claude"] = {
        "configured": bool(claude_key),
        "model": os.getenv("CLAUDE_MODEL", "claude-sonnet-4-20250514"),
    }

    # Ollama
    ollama_url = os.getenv("OLLAMA_URL", "http://localhost:11434")
    ollama_reachable = False
    if requests:
        try:
            resp = requests.get(f"{ollama_url}/api/tags", timeout=3)
            ollama_reachable = resp.status_code == 200
        except Exception:
            pass
    status["ollama"] = {
        "configured": ollama_reachable,
        "url": ollama_url,
        "model": os.getenv("OLLAMA_MODEL", "llama3.1"),
    }

    return status


def main():
    parser = argparse.ArgumentParser(description="ASPIRE AI Bridge")
    parser.add_argument("--action", required=True,
                        choices=["generate", "generate_json", "batch", "status"],
                        help="Action to perform")
    parser.add_argument("--provider", help="AI provider (gemini, openai, claude, ollama)")
    parser.add_argument("--model", help="Model name")
    parser.add_argument("--prompt", help="Prompt text")
    parser.add_argument("--temperature", type=float, default=0.5)
    parser.add_argument("--max-tokens", type=int, default=1024)
    parser.add_argument("--api-key", default="", help="API key override")
    parser.add_argument("--config", help="Path to JSON config for batch processing")
    parser.add_argument("--fallback", help="JSON array of fallback chain")

    args = parser.parse_args()

    try:
        if args.action == "status":
            result = check_provider_status()
            print(json.dumps(result, indent=2))

        elif args.action == "generate":
            if not args.provider or not args.model or not args.prompt:
                print(json.dumps({"success": False, "error": "--provider, --model, and --prompt are required"}))
                sys.exit(1)

            result = generate(
                provider=args.provider,
                model=args.model,
                prompt=args.prompt,
                temperature=args.temperature,
                max_tokens=args.max_tokens,
                api_key=args.api_key,
            )
            print(json.dumps(result, indent=2))

        elif args.action == "generate_json":
            if not args.provider or not args.model or not args.prompt:
                print(json.dumps({"success": False, "error": "--provider, --model, and --prompt are required"}))
                sys.exit(1)

            json_prompt = args.prompt + "\n\nRespond with valid JSON only, no markdown formatting, no code blocks."
            result = generate(
                provider=args.provider,
                model=args.model,
                prompt=json_prompt,
                temperature=args.temperature,
                max_tokens=args.max_tokens,
                api_key=args.api_key,
            )

            if result.get("success") and result.get("text"):
                import re
                text = result["text"].strip()
                text = re.sub(r'^```(?:json)?\s*|\s*```$', '', text)
                try:
                    result["json"] = json.loads(text)
                except json.JSONDecodeError:
                    result["json"] = None
                    result["json_error"] = "Failed to parse JSON response"

            print(json.dumps(result, indent=2))

        elif args.action == "batch":
            if not args.config:
                print(json.dumps({"success": False, "error": "--config is required for batch processing"}))
                sys.exit(1)

            with open(args.config, "r") as f:
                batch_config = json.load(f)

            results = batch_process(batch_config)
            print(json.dumps(results, indent=2))

    except Exception as e:
        print(json.dumps({
            "success": False,
            "error": str(e),
            "traceback": traceback.format_exc(),
        }))
        sys.exit(1)


if __name__ == "__main__":
    main()
