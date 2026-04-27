// Fungsi untuk memutar alarm
function playAlarm() {
    let audio = new Audio('https://www.soundjay.com/misc/sounds/bell-ringing-05.mp3');
    audio.loop = true;
    audio.play().catch(e => console.log("Autoplay diblokir, klik halaman"));
    document.body.addEventListener('click', () => audio.play());
}
// Jika ada elemen dengan class 'alarm-trigger', mainkan alarm
if(document.querySelector('.alarm-trigger')) {
    playAlarm();
}