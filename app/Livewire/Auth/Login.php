<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public bool $showPassword = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    protected array $messages = [
        'email.required' => 'Alamat email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'password.required' => 'Kata sandi wajib diisi.',
        'password.min' => 'Kata sandi minimal 6 karakter.',
    ];

    public function togglePasswordVisibility(): void
    {
        $this->showPassword = !$this->showPassword;
    }

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Berhasil Masuk! Selamat datang kembali.'
            ]);

            return redirect()->intended(route('dashboard'));
        }

        $this->addError('email', 'Kredensial email atau password tidak cocok.');

        $this->dispatch('swal:toast', [
            'icon' => 'error',
            'title' => 'Gagal Masuk! Periksa email dan kata sandi Anda.'
        ]);
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.app', ['title' => 'Login Admin']);
    }
}
