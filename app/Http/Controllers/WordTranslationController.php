<?php

namespace App\Http\Controllers;

use App\Services\OpenRouterService;
use App\Repositories\WordRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class WordTranslationController extends Controller
{
    protected OpenRouterService $openRouterService;
    protected WordRepository $wordRepository;

    public function __construct(
        OpenRouterService $openRouterService,
        WordRepository    $wordRepository
    )
    {
        $this->openRouterService = $openRouterService;
        $this->wordRepository = $wordRepository;
    }

    /**
     * ترجمه و ذخیره کلمات
     * POST /api/words/translate
     */
    public function translate(Request $request): JsonResponse
    {
//        $validator = Validator::make($request->all(), [
//            'words' => 'required|array|min:1|max:20',
//            'words.*' => 'required|string|max:100',
//            'model' => 'nullable|string',
//        ]);

//        if ($validator->fails()) {
//            return response()->json([
//                'success' => false,
//                'errors' => $validator->errors(),
//            ], 422);
//        }

        $words = ['Hello','The'];

        try {
            $model = 'google/gemma-3-12b-it';


            Log::info('Translating words', ['words' => $words]);

            $yamlResponse = $this->openRouterService->translateWords($words, $model);

            // ذخیره در دیتابیس
            $savedWords = $this->wordRepository->saveFromYaml($yamlResponse);

            return response()->json([
                'success' => true,
                'message' => 'Words translated and saved successfully',
                'data' => [
                    'words' => $savedWords,
                    'raw_yaml' => $yamlResponse,
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Translation failed', [
                'error' => $e->getMessage(),
                'words' => $request->input('words'),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * دریافت اطلاعات یک کلمه
     * GET /api/words/{word}
     */
    public function show(string $word): JsonResponse
    {
        try {
            $wordModel = $this->wordRepository->getWord($word);

            if (!$wordModel) {
                return response()->json([
                    'success' => false,
                    'error' => 'Word not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $wordModel->getFullData(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve word', [
                'word' => $word,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve word',
            ], 500);
        }
    }

    /**
     * لیست تمام کلمات
     * GET /api/words
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 50);
            $words = $this->wordRepository->getAllWords($perPage);

            return response()->json([
                'success' => true,
                'data' => $words,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve words',
            ], 500);
        }
    }

    /**
     * جستجوی کلمات
     * GET /api/words/search?q=query
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $query = $request->input('q');
            $perPage = $request->input('per_page', 20);

            $results = $this->wordRepository->searchWords($query, $perPage);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Search failed',
            ], 500);
        }
    }

    /**
     * حذف یک کلمه
     * DELETE /api/words/{word}
     */
    public function destroy(string $word): JsonResponse
    {
        try {
            $wordModel = $this->wordRepository->getWord($word);

            if (!$wordModel) {
                return response()->json([
                    'success' => false,
                    'error' => 'Word not found',
                ], 404);
            }

            $wordModel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Word deleted successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete word', [
                'word' => $word,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete word',
            ], 500);
        }
    }
}
