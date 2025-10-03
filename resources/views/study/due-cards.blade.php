<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperMemo Vt Flash Cards</title>
    <link href="{{url('lib/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{url('lib/fonts.css')}}" rel="stylesheet">
    <link href="{{url('lib/github-dark.min.css')}}" rel="stylesheet">
    <link href="{{url('lib/style.css')}}" rel="stylesheet">
    <style>
        .card-content {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #007bff;
        }

        .card-front {
            border-left-color: #28a745;
        }

        .card-back {
            border-left-color: #dc3545;
        }

        .markdown-content h1, .markdown-content h2, .markdown-content h3 {
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .markdown-content pre {
            background: #f1f3f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }

        .markdown-content code {
            background: #e9ecef;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 0.9em;
        }

        .markdown-content pre code {
            background: transparent;
            padding: 0;
        }

        .card-item {
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .card-header-info {
            background: #e9ecef;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>
<body dir="auto">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Due Cards</h3>
                    <form method="GET" action="{{ route('due-cards') }}" class="mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="path">Deck Path:</label>
                                    <input type="text" id="path" name="path" class="form-control"
                                           value="{{ $path ?? '' }}" placeholder="Enter deck path (optional)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="due_date">Due Date:</label>
                                    <input type="datetime-local" id="due_date" name="due_date" class="form-control"
                                           value="{{ $time }}">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter Cards</button>
                    </form>
                </div>
                <div class="card-body">
                    @if($deck)
                        <h4>Deck: {{ $deck->name }}</h4>
                    @else
                        <h4>All Decks</h4>
                    @endif

                    @if($relearningCards->count() > 0)
                        <div class="mb-4">
                            <h4 class="text-danger">Relearning Cards ({{ $relearningCards->count() }})</h4>
                            @foreach($relearningCards as $card)
                                <div class="card-item">
                                    <div class="card-header-info">
                                        <strong>Card ID: {{ $card->id }}</strong>
                                        <small class="text-muted float-end">Due
                                            Date: {{ $card->userReview->due_date ?? 'N/A' }}</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-content card-front">
                                            <h6 class="text-success mb-2">Front:</h6>
                                            <div class="markdown-content">{{ $card->front }}</div>
                                        </div>
                                        <div class="card-content card-back">
                                            <h6 class="text-danger mb-2">Back:</h6>
                                            <div class="markdown-content">{{ $card->back }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($learningCards->count() > 0)
                        <div class="mb-4">
                            <h4 class="text-warning">Learning Cards ({{ $learningCards->count() }})</h4>
                            @foreach($learningCards as $card)
                                <div class="card-item">
                                    <div class="card-header-info">
                                        <strong>Card ID: {{ $card->id }}</strong>
                                        <small class="text-muted float-end">Due
                                            Date: {{ $card->userReview->due_date ?? 'N/A' }}</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-content card-front">
                                            <h6 class="text-success mb-2">Front:</h6>
                                            <div class="markdown-content">{{ $card->front }}</div>
                                        </div>
                                        <div class="card-content card-back">
                                            <h6 class="text-danger mb-2">Back:</h6>
                                            <div class="markdown-content">{{ $card->back }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($reviewCards->count() > 0)
                        <div class="mb-4">
                            <h4 class="text-info">Review Cards ({{ $reviewCards->count() }})</h4>
                            @foreach($reviewCards as $card)
                                <div class="card-item">
                                    <div class="card-header-info">
                                        <strong>Card ID: {{ $card->id }}</strong>
                                        <small class="text-muted float-end">Due
                                            Date: {{ $card->userReview->due_date ?? 'N/A' }}</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-content card-front">
                                            <h6 class="text-success mb-2">Front:</h6>
                                            <div class="markdown-content">{{ $card->front }}</div>
                                        </div>
                                        <div class="card-content card-back">
                                            <h6 class="text-danger mb-2">Back:</h6>
                                            <div class="markdown-content">{{ $card->back }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($newCards->count() > 0)
                        <div class="mb-4">
                            <h4 class="text-success">New Cards ({{ $newCards->count() }})</h4>
                            @foreach($newCards as $card)
                                <div class="card-item">
                                    <div class="card-header-info">
                                        <strong>Card ID: {{ $card->id }}</strong>
                                        <small class="text-muted float-end">Due
                                            Date: {{ $card->userReview->due_date ?? 'N/A' }}</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-content card-front">
                                            <h6 class="text-success mb-2">Front:</h6>
                                            <div class="markdown-content">{{ $card->front }}</div>
                                        </div>
                                        <div class="card-content card-back">
                                            <h6 class="text-danger mb-2">Back:</h6>
                                            <div class="markdown-content">{{ $card->back }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($relearningCards->count() == 0 && $learningCards->count() == 0 && $reviewCards->count() == 0 && $newCards->count() == 0)
                        <div class="alert alert-info">
                            <h4>No Due Cards</h4>
                            <p>There are no cards due for review at this time.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{url('lib/marked.min.js')}}"></script>
<script src="{{url('lib/highlight.min.js')}}"></script>
<script src="{{url('lib/bootstrap.bundle.min.js')}}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configure marked to use highlight.js
        marked.setOptions({
            highlight: function(code, lang) {
                if (lang && hljs.getLanguage(lang)) {
                    return hljs.highlight(code, { language: lang }).value;
                }
                return hljs.highlightAuto(code).value;
            },
            breaks: true,
            gfm: true
        });

        // Function to detect Persian characters
        function hasPersian(text) {
            const persianRegex = /[\u0600-\u06FF]/;
            return persianRegex.test(text);
        }

        // Function to set RTL direction for lines with Persian characters
        function setDirectionForPersianLines(element) {
            const lines = element.innerHTML.split('\n');
            const processedLines = lines.map(line => {
                if (hasPersian(line)) {
                    return `<div style="direction: rtl; text-align: right;">${line}</div>`;
                }
            });
            element.innerHTML = processedLines.join('');
        }

        // Parse all markdown content
        document.querySelectorAll('.markdown-content').forEach(function(element) {
            const markdown = element.textContent;
            element.innerHTML = marked.parse(markdown);

            // Apply RTL direction for Persian content
            setDirectionForPersianLines(element);
        });
    });
</script>
