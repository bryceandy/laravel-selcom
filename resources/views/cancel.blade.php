<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
        <title>Selcom | Payment Cancelled</title>
    </head>
    <body>
        <section class="text-gray-600 body-font">
            <div class="container px-5 py-24 mx-auto flex flex-wrap">
                <h2 class="sm:text-5xl text-7xl text-gray-900 text-center font-bold title-font mb-2 md:w-2/5">
                    {{ config('app.name') }}
                </h2>
                <div class="md:w-3/5 md:pl-6">
                    <p class="leading-relaxed text-base">
                        Your payment is incomplete. Would you like to go back and try again or return home?
                    </p>
                    <div class="flex md:mt-4 mt-6">
                        <a
                            href="{{ url()->previous() }}"
                            class="inline-flex text-white bg-indigo-500 border-0 py-1 px-4 focus:outline-none
                            hover:bg-indigo-600 rounded"
                        >
                            Try Again
                        </a>
                        <a class="text-indigo-500 inline-flex items-center ml-4" href="/">Go Home
                            <svg
                                fill="none"
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                class="w-4 h-4 ml-2"
                                viewBox="0 0 24 24"
                            >
                                <path d="M5 12h14M12 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </body>
</html>
