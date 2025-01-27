function copyQuranVerse() {
    var verseText = document.getElementById('dqv-quran-verse-text').innerText;
    var tempInput = document.createElement('input');
    tempInput.value = verseText;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    alert('Verse copied to clipboard!');
}
