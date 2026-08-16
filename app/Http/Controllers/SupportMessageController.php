<?php

namespace App\Http\Controllers;

use App\Enums\NotificationType;
use App\Models\SupportMessage;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class SupportMessageController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    public function index(Request $request)
    {
        $messages = SupportMessage::query()
            ->forUser($request->user())
            ->latest()
            ->paginate(10);

        return view('support.index', $this->viewData($request->user(), ['messages' => $messages]));
    }

    public function create(Request $request)
    {
        return view('support.create', $this->viewData($request->user()));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:bug,feedback,suggestion'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = SupportMessage::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        $this->notificationService->notifyByRole(
            'admin',
            NotificationType::SUPPORT_MESSAGE,
            $this->titleFor($message),
            "{$request->user()->name} ({$request->user()->role}): {$message->subject}",
            null,
            route('admin.support-messages.show', $message)
        );

        return redirect()
            ->route('support.show', $message)
            ->with('success', 'Thank you! Your message has been sent to the administrators.');
    }

    public function show(Request $request, SupportMessage $supportMessage)
    {
        if ((int) $supportMessage->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        return view('support.show', $this->viewData($request->user(), ['message' => $supportMessage]));
    }

    protected function titleFor(SupportMessage $message): string
    {
        return match ($message->type) {
            SupportMessage::TYPE_BUG => 'New Bug Report',
            SupportMessage::TYPE_SUGGESTION => 'New Suggestion',
            default => 'New Feedback',
        };
    }

    protected function viewData(User $user, array $extra = []): array
    {
        return array_merge($extra, [
            'layout' => match ($user->role) {
                'admin' => 'layouts.admin',
                'supervisor' => 'layouts.supervisor',
                default => 'layouts.teacher',
            },
        ]);
    }
}
