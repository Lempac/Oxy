<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const language = ref('En');
const changeLanguage = (lang: string) => {
  language.value = lang;
};
</script>
<template>
    <Head title="Manual"></Head>
    <body class="bg-gray-700 flex flex-col min-h-screen">
      <div class="card card-body flex-grow">
        <header class="flex justify-between items-center">
          <Link :href="route('welcome')">
            <ApplicationLogo class="navbar-center mb-1.5 tooltip tooltip-bottom" data-tip="Home" />
          </Link>
          <div class="join ml-auto">
            <input class="join-item btn btn-square" type="radio" name="Language" aria-label="En" :checked="language === 'En'" @click="changeLanguage('En')" />
            <input class="join-item btn btn-square" type="radio" name="Language" aria-label="Lv" :checked="language === 'Lv'" @click="changeLanguage('Lv')" />
          </div>
        </header>
  
        <main>

            <h1 class="text-7xl font-sans text-white h-full text-center mb-10">Get Help ↓</h1>

            <!--Servers-->
            <div tabindex="0" class="collapse collapse-arrow border bg-slate-600 border-black mb-5">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white"> {{ language === 'En' ? 'Servers' : 'Serveri' }}</div>
            <div class="collapse-content">
            <div class="collapse collapse-arrow bg-slate-600 mb-5 border border-white">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Creating a server' : 'Servera izveidošana' }}</div>
            <div class="collapse-content">
                <h1>{{ language === 'En' ? 'To create a server:' : 'Lai izveidotu serveri:' }}</h1>
                <p class="text-white mb-2">1. {{ language === 'En' ? 'Press the "+" button on the main page' : 'Nospiedat "+" pogu sākumlapā' }}</p>
                <img src="/images/AddServer.png" class="block w-full fill-current mb-2"/>
                <p class="text-white mb-2">2. {{ language === 'En' ? 'On the "Create Server" tab input your server information and click "Create Server" to make your server' : '"Create Server" sadaļā ievadiet jūsu servera informāciju un spiežiet "Create Server" pogu lai izveidotu savu serveri' }}</p>
                <img src="/images/CreateServer.png" class="block w-auto mb-2 h-80"/>
            </div>
            </div>
            <div class="collapse collapse-arrow bg-slate-600 mb-5 border border-white">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white ">{{ language === 'En' ? 'Joining a server' : 'Pievienošanās serverim' }}</div>
            <div class="collapse-content">
                <h1>{{ language === 'En' ? 'To Join a server:' : 'Lai pievienotos serverim:' }}</h1>
                <p class="text-white mb-2">1. {{ language === 'En' ? 'Ask the server admin for the server code. The server code can be found here:' : 'Jautājat servera administratoram, lai jums atsūta servera kodu. Servera kods ir atrodams šeit:' }}</p>
                <img src="/images/ServerCode.png" class="block w-auto h-80 mb-2"/>
                <p class="text-white mb-2">2. {{ language === 'En' ? 'Press the "+" button on the main page' : 'Nospiediet "+" pogu sākumlapā' }}</p>
                <img src="/images/AddServer.png" class="block w-full fill-current mb-2"/>
                <p class="text-white mb-2">3. {{ language === 'En' ? 'On the "Join Server" tab input the server code and click "Join Server"' : '"Join Server" sadaļā ievadiet servera kodu un spiežiet "Join Server" pogu' }}</p>
                <img src="/images/JoinServer.png" class="block w-auto mb-2 h-80"/>
            </div>
            </div>
            </div>
            </div>

            <!--Channels-->
            <div tabindex="1" class="collapse collapse-arrow border bg-slate-600 border-black mb-5">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Channels' : 'Kanāli' }}</div>
            <div class="collapse-content">
                <div tabindex="1" class="collapse collapse-arrow border bg-slate-600 border-white mb-5">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Create a channel' : 'Kanālu izveidošana' }}</div>
            <div class="collapse-content">
                <h1>{{ language === 'En' ? 'To create a channel:' : 'Lai izveidotu kanālu: ' }}</h1>
                <p class="text-white mb-2">1. {{ language === 'En' ? 'On the channel page click on a channel and press the "+" button to create a channel' : 'Kanālu lapā nopsied kanālu un tad nospied "+" pogu, lai izveidotu kanālu' }}</p>
                <img src="/images/Channels.png" class="block w-auto fill-current mb-2"/>
                <p class="text-white mb-2">2. {{ language === 'En' ? 'Input a channel name and click "Create Text Channel"' : 'Ievadiet kanālam nosaukumu un spied "Create Text Channel"' }}</p>
                <img src="/images/CreateText.png" class="block w-auto mb-2 h-40"/>
            </div>
            </div>
            <div tabindex="1" class="collapse collapse-arrow border bg-slate-600 border-white mb-5">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Edit/Delete channels' : 'Kanālu rediģēšana/dzēšana' }}</div>
            <div class="collapse-content">
                <h1>{{ language === 'En' ? 'To edit a channel:' : 'Lai rediģētu kanālu:' }}</h1>
                <p class="text-white mb-2">1. {{ language === 'En' ? 'Hover over the channel you wish to edit and click the edit button (pencil icon)' : 'Novietojat kursoru uz kanālu kuri gribi rediģēt un spied rediģēšanas pogu (zīmuļu ikona)' }}</p>
                <img src="/images/TextChannel.png" class="block w-auto mb-2 h-60"/>
                <p class="text-white mb-2">2. {{ language === 'En' ? 'Change the channels name and click "Edit Text Channel"' : 'Rediģē kanāla nosaukumu un spied "Edit Text Channel"' }}</p>
                <img src="/images/RenameText.png" class="block w-auto mb-2 h-60"/>
                <h1 class="mt-5">{{ language === 'En' ? 'To delete a channel:' : 'Lai dzēstu kanālu:' }}</h1>
                <p class="text-white mb-2">1. {{ language === 'En' ? 'Hover over the channel you wish to delete and click the delete button (trash icon)' : 'Novietojat kursoru uz kanālu kuri gribi dzēst un spied dzēšanas pogu (miskastes ikona)' }}</p>
                <img src="/images/TextChannel.png" class="block w-auto mb-2 h-60"/>
                <p class="text-white mb-2">2. {{ language === 'En' ? 'Click "Yes" to delete the channel' : 'Spied "Yes" pogu lai izdēstu kanālu' }}</p>
                <img src="/images/DeleteChannel.png" class="block w-auto mb-2 h-40"/>
            </div>
            </div>
            </div>
            </div>

            <!-- Messages-->
            <div tabindex="2" class="collapse collapse-arrow border bg-slate-600 border-black mb-5">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Messages' : 'Īsziņas' }}</div>
            <div class="collapse-content">
                <h1> {{ language === 'En' ? 'To edit your message:' : 'Lai rediģētu savas īsziņas:' }}</h1>
                <p class="text-white mb-2">1. {{ language === 'En' ? 'Send a message in a text channel' : 'Uzraksti īsziņu tekstu kanālā' }}</p>
                <img src="/images/Texting.png" class="block w-auto mb-2 h-60"/>
                <p class="text-white mb-2">2. {{ language === 'En' ? 'Edit the message by hovering over it and clicking the pencil icon' : 'Rediģē īsziņu novietojot kursoru uz tās un nospied zīmuļu ikonu' }}</p>
                <img src="/images/EditText.png" class="block w-auto mb-2 h-40"/>
                <p class="text-white mb-2">3. {{ language === 'En' ? 'Edit the text and click "Edit Message"' : 'Rediģē tekstu un nospied "Edit Message" pogu' }}</p>
                <img src="/images/EditMessage.png" class="block w-auto mb-2 h-40"/>
                <p class="text-white mb-2">4. {{ language === 'En' ? 'To delete the message, hover over it and click the trash icon. Hit "Yes" and the message will be deleted' : 'Lai izdzēstu īsziņu, novietojiet kursoru uz tās un spied miskastes ikonu un "Yes" pogu' }}</p>
                <img src="/images/DeleteMessage.png" class="block w-auto mb-2 h-40"/>
            </div>
            </div>

            <!--Server settings-->
            <div tabindex="3" class="collapse collapse-arrow border bg-slate-600 border-black mb-5">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Server settings' : 'Servera iestatījumi' }}</div>
            <div class="collapse-content">
            <div class="collapse collapse-arrow border bg-slate-600 border-white mb-5">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Settings' : 'Iestatījumi' }}</div>
            <div class="collapse-content">
                <h1>{{ language === 'En' ? 'To access your servers settings:' : 'Lai piekļūtu servera iestatījumiem:' }}</h1>
                <p class="text-white mb-2">1. {{ language === 'En' ? 'On the channel bar click the gear icon' : 'Pie kanāliem noklikšķiniet uz zobrata ikonas' }}</p>
                <img src="/images/SettingsIcon.png" class="block w-full fill-current mb-2"/>
                <p class="text-white mb-2">2. {{ language === 'En' ? 'In the server settings page you can edit the basic information about the server, as well as permissions' : 'Servera iestatījumos varat rediģēt servera pamata informāciju, kā arī atļaujas' }}</p>
                <img src="/images/Settings.png" class="block w-auto mb-2 h-[600px]"/>
            </div>
            </div>

            <!--Roles-->
            <div class="collapse collapse-arrow border bg-slate-600 border-white mb-5">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Roles' : 'Lomas' }}</div>
            <div class="collapse-content">
                <h1>{{ language === 'En' ? 'To access your servers roles:' : 'Lai piekļūtu servera lomām:' }}</h1>
                <p class="text-white mb-2">1. {{ language === 'En' ? 'On the channel bar click the gear icon' : 'Pie kanāliem noklikšķiniet uz zobrata ikonas' }}</p>
                <img src="/images/SettingsIcon.png" class="block w-full fill-current mb-2"/>
                <p class="text-white mb-2">2. {{ language === 'En' ? 'In the server settings page, click the "Roles" button.' : 'Servera iestatījumu lapā noklikšķiniet uz "Roles" pogas.' }}</p>
                <img src="/images/ToRoles.png" class="block w-auto mb-2 h-80"/>
                <p class="text-white mb-2">3. {{ language === 'En' ? 'To create a role, press "Add Role".' : 'Lai izveidotu lomu spiežiet "Add Role" pogu.' }}</p>
                <img src="/images/Roles.png" class="block w-auto mb-2 h-80"/>
                <p class="text-white mb-2">4. {{ language === 'En' ? 'Input your roles information and press "Add Role".' : 'Ievadi lomas informāciju un spied "Add Role".' }}</p>
                <img src="/images/NewRole.png" class="block w-auto mb-2 h-80"/>
            </div>
            </div>
            <!--Delete serv-->
            <div class="collapse collapse-arrow border bg-slate-600 border-white mb-5">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Deleting a server' : 'Servera dzēšana' }}</div>
            <div class="collapse-content">
                <h1>{{ language === 'En' ? 'To delete your server:' : 'Lai dzēstu serveri:' }}</h1>
                <p class="text-white mb-2">1. {{ language === 'En' ? 'On the channel bar click the gear icon' : 'Pie kanāliem noklikšķiniet uz zobrata ikonas' }}</p>
                <img src="/images/SettingsIcon.png" class="block w-full fill-current mb-2"/>
                <p class="text-white mb-2">2. {{ language === 'En' ? 'In the server settings page, scroll down to the bottom and click "Delete Server". Click confirm and it will delete the server and all your channels (This cannot be undone).' : 'Iestatījumu lapas apakšā noklikšķiniet uz "Delete Server" pogas un apstiprinat.' }}</p>
                <img src="/images/DeleteServer.png" class="block w-auto mb-2 h-80"/>
            </div>
            </div>
            </div>
            </div>

            <!--Account-->
            <div tabindex="5" class="collapse collapse-arrow border bg-slate-600 border-black mb-5">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Creating an account' : 'Izveidot kontu' }}</div>
            <div class="collapse-content">
                <h1>{{ language === 'En' ? 'To Create an account:' : 'Lai izveidotu kontu:' }}</h1>
                <p class="text-white mb-2">1. {{ language === 'En' ? 'In the home page, click "Register"' : 'Mājas lapā spied pogu "Register"' }}</p>
                <img src="/images/CreateAccount.png" class="block w-auto mb-2 h-60"/>
                <p class="text-white mb-2">2. {{ language === 'En' ? 'Fill out the information and click "Register"' : 'Aizpildiet anketu ar nepieciešamo informāciju un nospiediet "Register"' }}</p>
                <img src="/images/Register.png" class="block w-auto mb-2 h-80"/>
                <p class="text-white mb-2">3. {{ language === 'En' ? 'Afterwards, go to your email and click "verify account" in our message' : 'Pēc reģistrēšanas, dodieties uz savu e-pastu un mūsu ziņojumā noklikšķiniet uz "verify account".' }}</p>
                <img src="/images/VerifyEmail.png" class="block w-auto mb-2 h-80"/>
            </div>
            </div>

            <!--Profile-->
            <div tabindex="6" class="collapse collapse-arrow border bg-slate-600 border-black mb-5">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Account' : 'Konts' }}</div>
            <div class="collapse-content">
            <div class="collapse collapse-arrow bg-slate-600 mb-5 border border-white">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Profile' : 'Profils' }}</div>
            <div class="collapse-content">
                <h1>{{ language === 'En' ? 'To go to your profile:' : 'Lai aizietu uz profilu:' }}</h1>
                <p class="text-white"> 1. {{ language === 'En' ? 'Click your profile picture, and it will reveal 2 option: "Profile" and "Log Out". Logging out will log you out of your current session. Clicking "Profile" will take you to the profile page.' : 'Noklikšķiniet uz sava profila attēla un tiks parādītas divas opcijas: "Profile" un "Log Out". Noklikšķinot uz "Profile" tiks atvērta profila lapa.' }}</p>
                <img src="/images/Profile.png" class="block w-auto mb-2 h-auto"/>
                <p class="text-white"> 2. {{ language === 'En' ? 'In the profile page you can change your information, as well as update your password and delete your account.' : 'Profila lapā varat mainīt savu informāciju, kā arī atjaunināt paroli un izdzēst savu kontu.' }}</p>
                <img src="/images/Profile info.png" class="block w-auto mb-2 h-80"/>
            </div>
            </div>
            <div class="collapse collapse-arrow bg-slate-600 mb-5 border border-white">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Update password' : 'Atjaunināt paroli' }}</div>
            <div class="collapse-content">
                <h1>{{ language === 'En' ? 'To update your accounts password:' : 'Lai atjauninātu sava konta paroli:' }}</h1>
                <p class="text-white"> 1. {{ language === 'En' ? 'Click on your profile picture and click "Profile"' : 'Noklikšķiniet uz sava profila attēla un spied "Profile" pogu' }}</p>
                <img src="/images/Profile.png" class="block w-auto mb-2 h-auto"/>
                <p class="text-white"> 2. {{ language === 'En' ? 'In the profile page scroll down to the "Update Password" section. Input the information and click "Save" to save you changes.' : 'Profila lapā aizejiet uz sadaļu "Update Password". Ievadiet informāciju un noklikšķiniet uz "Save", lai saglabātu izmaiņas.' }}</p>
                <img src="/images/UpdatePassword.png" class="block w-auto mb-2 h-80"/>
            </div>
            </div>
            <div class="collapse collapse-arrow bg-slate-600 mb-5 border border-white">
            <input type="checkbox" />
            <div class="collapse-title text-xl font-medium text-white">{{ language === 'En' ? 'Delete account' : 'Izdzēst kontu' }}</div>
            <div class="collapse-content">
                <h1>{{ language === 'En' ? 'To delete your account:' : 'Lai izdzēstu kontu:' }}</h1>
                <p class="text-white"> 1. {{ language === 'En' ? 'Click on your profile picture and click "Profile"' : 'Noklikšķiniet uz sava profila attēla un spied "Profile" pogu' }}</p>
                <img src="/images/Profile.png" class="block w-auto mb-2 h-auto"/>
                <p class="text-white"> 2. {{ language === 'En' ? 'In the profile page scroll down to the bottom to find the "Delete Account" section. Click the "Delete Account" button and hit confirm.' : 'Profila lapas apakšā, pie sadaļas "Delete Account", noklikšķiniet pogu "Delete Account" un apstipriniet.' }}</p>
                <img src="/images/DeleteAccount.png" class="block w-auto mb-2 h-40"/>
            </div>
            </div>
            </div>
            </div>
        </main>
  
        <footer class="footer footer-center mt-10 text-white">
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
