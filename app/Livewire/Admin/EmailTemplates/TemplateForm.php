<?php

declare(strict_types=1);

namespace App\Livewire\Admin\EmailTemplates;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TemplateForm extends Component
{
    public $template;

    public $testEmail = '';

    public $isEditing = false;

    public $previewHtml = '';

    protected $rules = [
        'template.subject' => 'required|string|max:255',
        'template.content' => 'required|string',
        'template.is_active' => 'boolean',
    ];

    public function mount(EmailTemplate $template)
    {
        $this->template = $template;
        $this->isEditing = $template->exists;
        $this->testEmail = auth()->user()->email;
    }

    public function save()
    {
        $this->validate();
        $this->template->save();

        session()->flash('status', __('Template saved!'));

        return redirect()->route('admin.email-templates.index');
    }

    public function sendTestEmail()
    {
        $this->validate([
            'testEmail' => 'required|email',
        ]);

        $data = $this->getMockData();

        $subject = $this->template->renderSubject($data);
        $content = $this->template->render($data);

        Mail::html($content, function ($message) use ($subject) {
            $message->to($this->testEmail)
                ->subject('[TEST] '.$subject);
        });

        session()->flash('test-status', __('Test email sent to :email', ['email' => $this->testEmail]));
    }

    public function generatePreview()
    {
        $this->previewHtml = $this->template->render($this->getMockData());
    }

    protected function getMockData(): array
    {
        return [
            'user_name' => auth()->user()->name,
            'user_email' => auth()->user()->email,
            'tenant_name' => tenant()->app_name ?? tenant()->name,
            'plan_name' => __('Premium Membership'),
            'class_name' => __('CrossFit WOD'),
            'class_date' => now()->addDay()->setHour(17)->setMinute(0)->format('d-m-Y H:i'),
        ];
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.email-templates.form');
    }
}
