<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?>Login PERJADIN<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');
        const eyeOffIcon = document.querySelector('#eyeOffIcon');

        if (togglePassword && password) {
            togglePassword.addEventListener('click', function() {
                // Toggle the type attribute
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);

                // Toggle the icons
                eyeIcon.classList.toggle('hidden');
                eyeOffIcon.classList.toggle('hidden');
            });
        }
        const loginForm = document.querySelector('form');
        const loginInput = document.querySelector('#login');

        if (loginForm && loginInput) {
            loginForm.addEventListener('submit', function() {
                // Determine if it's an email or username based on '@'
                if (loginInput.value.includes('@')) {
                    loginInput.setAttribute('name', 'email');
                } else {
                    loginInput.setAttribute('name', 'username');
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
    <div class="grid w-full max-w-5xl overflow-hidden rounded-md border border-surface-200 bg-white shadow-soft lg:grid-cols-2">
        <div class="hidden bg-linear-to-br from-secondary-100 via-surface-50 to-accent-100 p-10 lg:block">
            <p class="mb-4 inline-flex items-center rounded-sm bg-primary-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-900">
                Politeknik Negeri Sriwijaya
            </p>
            <h1 class="text-3xl font-bold leading-tight text-slate-900">Sistem PERJADIN</h1>
            <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-700">
                Kelola perjalanan dinas secara terstruktur, transparan, dan efisien dengan alur persetujuan berbasis peran.
            </p>
        </div>

        <div class="p-6 sm:p-10 lg:p-12">
            <h2 class="text-2xl font-semibold text-slate-900">Masuk ke akun</h2>
            <p class="mt-1 text-sm text-slate-600">Gunakan email dan password yang telah terdaftar.</p>

            <?php if (session('error') !== null) : ?>
                <div class="mt-6 rounded-md border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-800" role="alert">
                    <?= esc(session('error')) ?>
                </div>
            <?php elseif (session('errors') !== null) : ?>
                <div class="mt-6 rounded-md border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-800" role="alert">
                    <?php if (is_array(session('errors'))) : ?>
                        <?php foreach (session('errors') as $error) : ?>
                            <p><?= esc($error) ?></p>
                        <?php endforeach ?>
                    <?php else : ?>
                        <p><?= esc(session('errors')) ?></p>
                    <?php endif ?>
                </div>
            <?php endif ?>

            <?php if (session('message') !== null) : ?>
                <div class="mt-6 rounded-md border border-secondary-200 bg-secondary-50 px-4 py-3 text-sm text-secondary-800" role="alert">
                    <?= esc(session('message')) ?>
                </div>
            <?php endif ?>

            <form action="<?= url_to('login') ?>" method="post" class="mt-6 space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label for="login" class="form-label">Username atau Email</label>
                    <input
                        id="login"
                        name="login"
                        type="text"
                        value="<?= old('login', old('email', old('username'))) ?>"
                        inputmode="text"
                        autocomplete="username"
                        required
                        class="input-control"
                        placeholder="username atau nama@polsri.ac.id">
                </div>

                <div>
                    <label for="password" class="form-label">Password</label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            inputmode="text"
                            autocomplete="current-password"
                            required
                            class="input-control pe-10"
                            placeholder="••••••••">
                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden">
                            <i data-lucide="eye" class="h-5 w-5" id="eyeIcon"></i>
                            <i data-lucide="eye-off" class="hidden h-5 w-5" id="eyeOffIcon"></i>
                        </button>
                    </div>
                </div>

                <?php if (setting('Auth.sessionConfig')['allowRemembering']) : ?>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-secondary-300 text-accent-500 focus:ring-accent-200" <?= old('remember') ? 'checked' : '' ?>>
                        Ingat saya
                    </label>
                <?php endif; ?>

                <button type="submit" class="btn-accent w-full justify-center">Login</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>