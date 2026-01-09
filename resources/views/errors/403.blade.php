<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 | Forbidden</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-6">
    <div class="relative w-full max-w-2xl overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-[0_20px_70px_-30px_rgba(0,0,0,0.8)]">
        <div class="absolute inset-0 opacity-60" aria-hidden="true">
            <div class="absolute -left-20 -top-24 h-64 w-64 rounded-full bg-gradient-to-br from-orange-500/50 via-amber-400/40 to-rose-500/40 blur-3xl"></div>
            <div class="absolute -right-16 bottom-0 h-60 w-60 rounded-full bg-gradient-to-br from-emerald-400/40 via-cyan-400/30 to-blue-500/30 blur-3xl"></div>
        </div>

        <div class="relative grid gap-8 px-10 py-12 md:grid-cols-[1.2fr_1fr] md:items-center">
            <div class="space-y-4">
                <div class="inline-flex items-center gap-3 rounded-full bg-slate-800/70 px-4 py-2 text-sm font-semibold text-amber-200">
                    <i class="fa-solid fa-shield-alt"></i>
                    Access Restricted
                </div>
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Error 403</p>
                    <h1 class="mt-2 text-4xl font-black leading-tight text-white md:text-5xl">You don't have permission to view this page.</h1>
                </div>
                <p class="text-base text-slate-300">
                    Your account lacks access to this section. If you think this is a mistake, reach out to your administrator or sign in with an account that has the right permissions.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:-translate-y-0.5 hover:bg-amber-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Back to login
                    </a>
                    <a href="javascript:history.back()" class="inline-flex items-center gap-2 rounded-xl border border-slate-700 px-5 py-3 text-sm font-semibold text-slate-100 transition hover:-translate-y-0.5 hover:border-slate-500 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-400">
                        <i class="fa-solid fa-arrow-left"></i>
                        Go back
                    </a>
                </div>
            </div>

            <div class="relative flex justify-center md:justify-end">
                <div class="relative h-48 w-48 md:h-56 md:w-56">
                    <div class="absolute inset-0 rounded-3xl border border-amber-300/20 bg-gradient-to-br from-amber-500/20 via-orange-500/10 to-amber-500/0 blur-xl"></div>
                    <div class="relative flex h-full w-full items-center justify-center rounded-3xl border border-slate-800 bg-slate-950/70 shadow-inner shadow-amber-500/10">
                        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-slate-950 shadow-xl shadow-amber-500/30">
                            <i class="fa-solid fa-lock text-4xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
