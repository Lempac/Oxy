<script lang="ts" setup>
import {Head, Link, router, usePage} from '@inertiajs/vue3';
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const page = usePage();

function t(key: string) {
    const keys = key.split('.');
    let translation = page.props.translations;
    for (const k of keys) {
        if (translation && typeof translation === 'object' && k in (translation as object)) {
            translation = (translation as Record<string, unknown>)[k];
        } else {
            return key;
        }
    }
    return typeof translation == "string" ? translation : key;
}

function setLanguage(lang: string) {
    router.post(route('language.update'), {language: lang.toLowerCase()}, {
        preserveScroll: true,
        onSuccess: () => {
            // Locale updated
        }
    });
}

// Fallback if route() helper isn't globally available or ziggy is replaced by wayfinder
// Assuming Wayfinder setup from previous conversation 8eda478c...
// If a route helper is missing, we use direct string for now as a safe bet: '/language'
function route(name: string) {
    if (name === 'language.update') return '/language';
    return '';
}

const back = () => {
    window.history.back();
}

</script>
<template>
    <Head :title="t('manual.manual')"></Head>
    <body class="bg-base-200 flex flex-col min-h-screen">
    <div class="card card-body grow">
        <header class="flex justify-between items-center">
            <Link href="" @click="(_event: unknown) => back()">
                <ApplicationLogo :data-tip="t('manual.home')" class="navbar-center mb-1.5 tooltip tooltip-bottom"/>
            </Link>
            <div class="join ml-auto">
                <input
                    :checked="page.props.locale === 'en'" aria-label="En" class="join-item btn btn-square"
                    name="Language"
                    type="radio" @click="setLanguage('En')"/>
                <input
                    :checked="page.props.locale === 'lv'" aria-label="Lv" class="join-item btn btn-square"
                    name="Language"
                    type="radio" @click="setLanguage('Lv')"/>
            </div>
        </header>

        <main>

            <h1 class="text-7xl font-sans text-base-content h-full text-center mb-10">{{ t('manual.get_help') }}</h1>

            <!--Servers-->
            <div class="collapse collapse-arrow border bg-base-100 border-black mb-5" tabindex="0">
                <input type="checkbox"/>
                <div class="collapse-title text-xl font-medium text-base-content">
                    {{ t('manual.servers') }}
                </div>
                <div class="collapse-content">
                    <div class="collapse collapse-arrow bg-base-100 mb-5 border border-white">
                        <input type="checkbox"/>
                        <div class="collapse-title text-xl font-medium text-base-content">
                            {{ t('manual.creating_a_server') }}
                        </div>
                        <div class="collapse-content">
                            <h1>{{ t('manual.to_create_a_server') }}</h1>
                            <p class="text-base-content mb-2">{{ t('manual.press_plus') }}</p>
                            <img alt="" class="block w-full fill-current mb-2" src="/images/AddServer.png"/>
                            <p class="text-base-content mb-2">{{ t('manual.on_create_server') }}</p>
                            <img alt="" class="block w-auto mb-2 h-80" src="/images/CreateServer.png"/>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow bg-base-100 mb-5 border border-white">
                        <input type="checkbox"/>
                        <div class="collapse-title text-xl font-medium text-base-content ">
                            {{ t('manual.joining_a_server') }}
                        </div>
                        <div class="collapse-content">
                            <h1>{{ t('manual.to_join_a_server') }}</h1>
                            <p class="text-base-content mb-2">{{ t('manual.ask_server_admin') }}</p>
                            <img alt="" class="block w-auto h-80 mb-2" src="/images/ServerCode.png"/>
                            <p class="text-base-content mb-2">{{ t('manual.join_press_plus') }}</p>
                            <img alt="" class="block w-full fill-current mb-2" src="/images/AddServer.png"/>
                            <p class="text-base-content mb-2">{{ t('manual.on_join_server') }}</p>
                            <img alt="" class="block w-auto mb-2 h-80" src="/images/JoinServer.png"/>
                        </div>
                    </div>
                </div>
            </div>

            <!--Channels-->
            <div class="collapse collapse-arrow border bg-base-100 border-black mb-5" tabindex="1">
                <input type="checkbox"/>
                <div class="collapse-title text-xl font-medium text-base-content">
                    {{ t('manual.channels') }}
                </div>
                <div class="collapse-content">
                    <div class="collapse collapse-arrow border bg-base-100 border-white mb-5" tabindex="1">
                        <input type="checkbox"/>
                        <div class="collapse-title text-xl font-medium text-base-content">
                            {{ t('manual.create_a_channel') }}
                        </div>
                        <div class="collapse-content">
                            <h1>{{ t('manual.to_create_a_channel') }}</h1>
                            <p class="text-base-content mb-2">{{ t('manual.on_channel_page') }}</p>
                            <img alt="" class="block w-auto fill-current mb-2" src="/images/Channels.png"/>
                            <p class="text-base-content mb-2">{{ t('manual.input_channel_name') }}</p>
                            <img alt="" class="block w-auto mb-2 h-40" src="/images/CreateText.png"/>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow border bg-base-100 border-white mb-5" tabindex="1">
                        <input type="checkbox"/>
                        <div class="collapse-title text-xl font-medium text-base-content">
                            {{ t('manual.edit_delete_channels') }}
                        </div>
                        <div class="collapse-content">
                            <h1>{{ t('manual.to_edit_a_channel') }}</h1>
                            <p class="text-base-content mb-2">{{ t('manual.hover_edit_channel') }}</p>
                            <img alt="" class="block w-auto mb-2 h-60" src="/images/TextChannel.png"/>
                            <p class="text-base-content mb-2">{{ t('manual.change_channel_name') }}</p>
                            <img alt="" class="block w-auto mb-2 h-60" src="/images/RenameText.png"/>
                            <h1 class="mt-5">{{ t('manual.to_delete_a_channel') }}</h1>
                            <p class="text-base-content mb-2">{{ t('manual.hover_delete_channel') }}</p>
                            <img alt="" class="block w-auto mb-2 h-60" src="/images/TextChannel.png"/>
                            <p class="text-base-content mb-2">{{ t('manual.click_yes_delete') }}</p>
                            <img alt="" class="block w-auto mb-2 h-40" src="/images/DeleteChannel.png"/>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages-->
            <div class="collapse collapse-arrow border bg-base-100 border-black mb-5" tabindex="2">
                <input type="checkbox"/>
                <div class="collapse-title text-xl font-medium text-base-content">
                    {{ t('manual.messages') }}
                </div>
                <div class="collapse-content">
                    <h1> {{ t('manual.to_edit_your_message') }}</h1>
                    <p class="text-base-content mb-2">{{ t('manual.send_message') }}</p>
                    <img alt="" class="block w-auto mb-2 h-60" src="/images/Texting.png"/>
                    <p class="text-base-content mb-2">{{ t('manual.edit_message_hover') }}</p>
                    <img alt="" class="block w-auto mb-2 h-40" src="/images/EditText.png"/>
                    <p class="text-base-content mb-2">{{ t('manual.edit_text_click') }}</p>
                    <img alt="" class="block w-auto mb-2 h-40" src="/images/EditMessage.png"/>
                    <p class="text-base-content mb-2">{{ t('manual.delete_message_hover') }}</p>
                    <img alt="" class="block w-auto mb-2 h-40" src="/images/DeleteMessage.png"/>
                </div>
            </div>

            <!--Server settings-->
            <div class="collapse collapse-arrow border bg-base-100 border-black mb-5" tabindex="3">
                <input type="checkbox"/>
                <div class="collapse-title text-xl font-medium text-base-content">
                    {{ t('manual.server_settings') }}
                </div>
                <div class="collapse-content">
                    <div class="collapse collapse-arrow border bg-base-100 border-white mb-5">
                        <input type="checkbox"/>
                        <div class="collapse-title text-xl font-medium text-base-content">
                            {{ t('manual.settings') }}
                        </div>
                        <div class="collapse-content">
                            <h1>{{ t('manual.to_access_settings') }}</h1>
                            <p class="text-base-content mb-2">{{ t('manual.channel_bar_gear') }}</p>
                            <img alt="" class="block w-full fill-current mb-2" src="/images/SettingsIcon.png"/>
                            <p class="text-base-content mb-2">{{ t('manual.in_server_settings') }}</p>
                            <img alt="" class="block w-auto mb-2 h-150" src="/images/Settings.png"/>
                        </div>
                    </div>

                    <!--Roles-->
                    <div class="collapse collapse-arrow border bg-base-100 border-white mb-5">
                        <input type="checkbox"/>
                        <div class="collapse-title text-xl font-medium text-base-content">
                            {{ t('manual.roles') }}
                        </div>
                        <div class="collapse-content">
                            <h1>{{ t('manual.to_access_roles') }}</h1>
                            <p class="text-base-content mb-2">{{ t('manual.channel_bar_gear') }}</p>
                            <img alt="" class="block w-full fill-current mb-2" src="/images/SettingsIcon.png"/>
                            <p class="text-base-content mb-2">{{ t('manual.in_server_settings_roles') }}</p>
                            <img alt="" class="block w-auto mb-2 h-80" src="/images/ToRoles.png"/>
                            <p class="text-base-content mb-2">{{ t('manual.to_create_role') }}</p>
                            <img alt="" class="block w-auto mb-2 h-80" src="/images/Roles.png"/>
                            <p class="text-base-content mb-2">{{ t('manual.input_roles_info') }}</p>
                            <img alt="" class="block w-auto mb-2 h-80" src="/images/NewRole.png"/>
                        </div>
                    </div>
                    <!--Delete serv-->
                    <div class="collapse collapse-arrow border bg-base-100 border-white mb-5">
                        <input type="checkbox"/>
                        <div class="collapse-title text-xl font-medium text-base-content">
                            {{ t('manual.deleting_a_server') }}
                        </div>
                        <div class="collapse-content">
                            <h1>{{ t('manual.to_delete_your_server') }}</h1>
                            <p class="text-base-content mb-2">{{ t('manual.channel_bar_gear') }}</p>
                            <img alt="" class="block w-full fill-current mb-2" src="/images/SettingsIcon.png"/>
                            <p class="text-base-content mb-2">{{ t('manual.in_server_settings_delete') }}</p>
                            <img alt="" class="block w-auto mb-2 h-80" src="/images/DeleteServer.png"/>
                        </div>
                    </div>
                </div>
            </div>

            <!--Account-->
            <div class="collapse collapse-arrow border bg-base-100 border-black mb-5" tabindex="5">
                <input type="checkbox"/>
                <div class="collapse-title text-xl font-medium text-base-content">
                    {{ t('manual.creating_an_account') }}
                </div>
                <div class="collapse-content">
                    <h1>{{ t('manual.to_create_an_account') }}</h1>
                    <p class="text-base-content mb-2">{{ t('manual.in_home_page_register') }}</p>
                    <img alt="" class="block w-auto mb-2 h-60" src="/images/CreateAccount.png"/>
                    <p class="text-base-content mb-2">{{ t('manual.fill_out_info') }}</p>
                    <img alt="" class="block w-auto mb-2 h-80" src="/images/Register.png"/>
                    <p class="text-base-content mb-2">{{ t('manual.afterwards_email') }}</p>
                    <img alt="" class="block w-auto mb-2 h-80" src="/images/VerifyEmail.png"/>
                </div>
            </div>

            <!--Profile-->
            <div class="collapse collapse-arrow border bg-base-100 border-black mb-5" tabindex="6">
                <input type="checkbox"/>
                <div class="collapse-title text-xl font-medium text-base-content">
                    {{ t('manual.account') }}
                </div>
                <div class="collapse-content">
                    <div class="collapse collapse-arrow bg-base-100 mb-5 border border-white">
                        <input type="checkbox"/>
                        <div class="collapse-title text-xl font-medium text-base-content">
                            {{ t('manual.profile') }}
                        </div>
                        <div class="collapse-content">
                            <h1>{{ t('manual.to_go_to_profile') }}</h1>
                            <p class="text-base-content"> {{ t('manual.click_profile_picture') }}</p>
                            <img alt="" class="block w-auto mb-2 h-auto" src="/images/Profile.png"/>
                            <p class="text-base-content"> {{ t('manual.in_profile_page_change') }}</p>
                            <img alt="" class="block w-auto mb-2 h-80" src="/images/Profile info.png"/>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow bg-base-100 mb-5 border border-white">
                        <input type="checkbox"/>
                        <div class="collapse-title text-xl font-medium text-base-content">
                            {{ t('manual.update_password') }}
                        </div>
                        <div class="collapse-content">
                            <h1>{{ t('manual.to_update_password') }}</h1>
                            <p class="text-base-content"> {{ t('manual.click_profile_profile') }}</p>
                            <img alt="" class="block w-auto mb-2 h-auto" src="/images/Profile.png"/>
                            <p class="text-base-content"> {{ t('manual.in_profile_page_update') }}</p>
                            <img alt="" class="block w-auto mb-2 h-80" src="/images/UpdatePassword.png"/>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow bg-base-100 mb-5 border border-white">
                        <input type="checkbox"/>
                        <div class="collapse-title text-xl font-medium text-base-content">
                            {{ t('manual.delete_account') }}
                        </div>
                        <div class="collapse-content">
                            <h1>{{ t('manual.to_delete_account') }}</h1>
                            <p class="text-base-content"> {{ t('manual.click_profile_profile') }}</p>
                            <img alt="" class="block w-auto mb-2 h-auto" src="/images/Profile.png"/>
                            <p class="text-base-content"> {{ t('manual.in_profile_page_delete') }}</p>
                            <img alt="" class="block w-auto mb-2 h-40" src="/images/DeleteAccount.png"/>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer footer-center mt-10 text-base-content">
            <div class="rounded-full p-4 bg-transparent text-center">
                © {{ new Date().getFullYear() }} Oxy
            </div>
        </footer>
    </div>
    </body>
</template>

<style scoped>

body {
    animation: fadeIn ease-in-out 2s;
}

@keyframes fadeIn {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

</style>
