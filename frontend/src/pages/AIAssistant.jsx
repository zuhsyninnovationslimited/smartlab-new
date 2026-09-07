import {
    useEffect,
    useRef,
    useState,
} from "react";

import {
    Bot,
    BrainCircuit,
    Send,
    ShieldCheck,
    Sparkles,
} from "lucide-react";

import api, {
    errorMessage,
} from "../services/api";

const normaliseArray = (value) => {
    if (Array.isArray(value)) {
        return value;
    }

    if (Array.isArray(value?.data)) {
        return value.data;
    }

    return [];
};

export default function AIAssistant() {
    const [messages, setMessages] = useState([]);
    const [experiments, setExperiments] = useState([]);
    const [experimentId, setExperimentId] = useState("");
    const [text, setText] = useState("");
    const [busy, setBusy] = useState(false);
    const [loading, setLoading] = useState(true);
    const [loadError, setLoadError] = useState("");
    const [assistantStatus, setAssistantStatus] = useState({
        available: true,
        configured: false,
        mode: "offline",
        provider: "SmartLab Offline Assistant",
        model: null,
    });

    const end = useRef(null);

    useEffect(() => {
        let mounted = true;

        const loadAssistant = async () => {
            setLoading(true);
            setLoadError("");

            const results = await Promise.allSettled([
                api.get("/ai/history"),
                api.get("/experiments"),
                api.get("/ai/status"),
            ]);

            if (!mounted) {
                return;
            }

            const [
                historyResult,
                experimentsResult,
                statusResult,
            ] = results;

            if (historyResult.status === "fulfilled") {
                setMessages(
                    normaliseArray(historyResult.value.data)
                );
            } else {
                setMessages([]);
            }

            if (experimentsResult.status === "fulfilled") {
                setExperiments(
                    normaliseArray(experimentsResult.value.data)
                );
            } else {
                setExperiments([]);
            }

            if (statusResult.status === "fulfilled") {
                setAssistantStatus(
                    statusResult.value.data
                );
            }

            const failedRequests = results.filter(
                (result) => result.status === "rejected"
            );

            if (failedRequests.length > 0) {
                setLoadError(
                    "Some AI information could not be loaded. " +
                    "You can still use the assistant."
                );
            }

            setLoading(false);
        };

        loadAssistant();

        return () => {
            mounted = false;
        };
    }, []);

    useEffect(() => {
        end.current?.scrollIntoView({
            behavior: "smooth",
        });
    }, [messages, busy]);

    const send = async (event) => {
        event.preventDefault();

        const message = text.trim();

        if (!message || busy) {
            return;
        }

        setText("");

        setMessages((current) => [
            ...current,
            {
                role: "user",
                content: message,
            },
        ]);

        setBusy(true);

        try {
            const response = await api.post("/ai/ask", {
                message,
                experiment_id: experimentId || null,
            });

            const answer =
                response.data?.answer ??
                "The assistant returned an empty response.";

            setMessages((current) => [
                ...current,
                {
                    role: "assistant",
                    content: answer,
                    provider:
                        response.data?.provider ??
                        assistantStatus.provider,
                },
            ]);

            if (response.data?.provider) {
                setAssistantStatus((current) => ({
                    ...current,
                    mode:
                        response.data.provider ===
                        "huggingface"
                            ? "huggingface"
                            : "offline",
                    configured:
                        response.data.provider ===
                        "huggingface",
                    provider:
                        response.data.provider ===
                        "huggingface"
                            ? "Hugging Face"
                            : "SmartLab Offline Assistant",
                    model:
                        response.data.model ??
                        current.model,
                }));
            }
        } catch (error) {
            setMessages((current) => [
                ...current,
                {
                    role: "assistant",
                    content: errorMessage(error),
                    provider: "error",
                },
            ]);
        } finally {
            setBusy(false);
        }
    };

    const statusText =
        assistantStatus.mode === "huggingface"
            ? `Hugging Face • ${
                  assistantStatus.model ??
                  "Inference Provider"
              }`
            : "Offline mode • no Hugging Face token";

    return (
        <div className="assistant-page">
            <section className="assistant-info">
                <span className="bot-hero">
                    <BrainCircuit />
                </span>

                <span className="eyebrow">
                    CONTEXT-AWARE TUTOR
                </span>

                <h1>SmartLab AI Assistant</h1>

                <p>
                    Ask for conceptual explanations,
                    equation guidance, graph interpretation,
                    procedural support, and laboratory safety
                    reminders.
                </p>

                <div className="guardrail">
                    <ShieldCheck />

                    <div>
                        <b>Assessment guardrail enabled</b>

                        <span>
                            The assistant explains principles
                            but does not reveal active graded
                            answers.
                        </span>
                    </div>
                </div>

                <div className="suggestions">
                    {[
                        "Explain limiting reagent simply",
                        "How does thickness affect conduction?",
                        "Help me interpret a nonlinear graph",
                        "What safety checks come first?",
                    ].map((suggestion) => (
                        <button
                            type="button"
                            key={suggestion}
                            onClick={() =>
                                setText(suggestion)
                            }
                        >
                            {suggestion}
                        </button>
                    ))}
                </div>
            </section>

            <section className="chat-workspace">
                <div className="chat-toolbar">
                    <div>
                        <span className="bot-orb">
                            <Bot />
                        </span>

                        <span>
                            <b>SmartLab AI</b>
                            <small>{statusText}</small>
                        </span>
                    </div>

                    <select
                        value={experimentId}
                        onChange={(event) =>
                            setExperimentId(
                                event.target.value
                            )
                        }
                    >
                        <option value="">
                            General laboratory context
                        </option>

                        {experiments.map((experiment) => (
                            <option
                                value={experiment.id}
                                key={experiment.id}
                            >
                                {experiment.title}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="chat-stream">
                    {loading && (
                        <div className="chat-empty">
                            <Sparkles />

                            <h3>
                                Loading the AI assistant…
                            </h3>
                        </div>
                    )}

                    {!loading && loadError && (
                        <div className="chat-empty">
                            <ShieldCheck />

                            <h3>
                                Limited assistant connection
                            </h3>

                            <p>{loadError}</p>
                        </div>
                    )}

                    {!loading &&
                        messages.length === 0 &&
                        !loadError && (
                            <div className="chat-empty">
                                <Sparkles />

                                <h3>
                                    What would you like to
                                    understand?
                                </h3>

                                <p>
                                    Choose a suggestion or ask
                                    a specific laboratory
                                    question.
                                </p>
                            </div>
                        )}

                    {messages.map((message, index) => (
                        <div
                            className={`chat-bubble ${
                                message.role === "user"
                                    ? "user"
                                    : "assistant"
                            }`}
                            key={
                                message.id ??
                                `${message.role}-${index}`
                            }
                        >
                            <span>
                                {message.role ===
                                "assistant" ? (
                                    <Bot />
                                ) : (
                                    "You"
                                )}
                            </span>

                            <p>
                                {String(
                                    message.content ?? ""
                                )}
                            </p>
                        </div>
                    ))}

                    {busy && (
                        <div className="chat-bubble assistant">
                            <span>
                                <Bot />
                            </span>

                            <p className="typing">
                                Preparing an explanation…
                            </p>
                        </div>
                    )}

                    <div ref={end} />
                </div>

                <form
                    className="chat-compose"
                    onSubmit={send}
                >
                    <textarea
                        value={text}
                        onChange={(event) =>
                            setText(event.target.value)
                        }
                        disabled={busy}
                        placeholder={
                            assistantStatus.mode ===
                            "huggingface"
                                ? "Ask the Hugging Face assistant about a concept, formula, graph, procedure, or safety issue…"
                                : "Ask the offline assistant about CO₂, heat transfer, formulas, procedures, or safety…"
                        }
                    />

                    <button
                        type="submit"
                        className="button primary"
                        disabled={busy || !text.trim()}
                        aria-label="Send question"
                    >
                        <Send />
                    </button>
                </form>
            </section>
        </div>
    );
}
