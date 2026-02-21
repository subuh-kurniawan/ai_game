<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Accounting Quiz Battle - Timer & Effect</title>
<style>
  body { font-family: Arial; background: #222; color: #fff; text-align: center; padding: 20px; }
  .container { max-width: 600px; margin: auto; background: #333; padding: 20px; border-radius: 10px; }
  h2 { margin-bottom: 10px; }
  .hp-bar { height: 20px; background: green; margin: 10px 0; border-radius: 5px; transition: width 0.5s; }
  .hp-bar.enemy { background: red; }
  .question { margin: 20px 0; font-size: 18px; }
  .options button { padding: 10px 20px; margin: 5px; cursor: pointer; border: none; border-radius: 5px; transition: transform 0.2s; }
  .options button:hover { transform: scale(1.05); }
  .feedback { margin-top: 10px; font-weight: bold; min-height: 24px; }
  .timer { font-weight: bold; margin-top: 10px; }
</style>
</head>
<body>

<div class="container">
  <h2>Accounting Quiz Battle</h2>

  <div>
    <p>Pemain HP:</p>
    <div class="hp-bar" id="playerHpBar" style="width: 100%;"></div>
  </div>

  <div>
    <p>Lawan HP:</p>
    <div class="hp-bar enemy" id="enemyHpBar" style="width: 100%;"></div>
  </div>

  <p class="question" id="questionText"></p>
  <div class="options" id="options"></div>
  <p class="timer" id="timer">Waktu tersisa: 15 detik</p>
  <p class="feedback" id="feedback"></p>
</div>

<script>
const questions = [
  {text: "Pemilik menyetor modal Rp10.000.000 tunai. Akun debit yang benar?", options:["Kas","Modal","Piutang","Pendapatan"], correct:"Kas"},
  {text: "Membeli perlengkapan Rp2.000.000 tunai. Akun kredit yang benar?", options:["Kas","Modal","Perlengkapan","Pendapatan"], correct:"Kas"},
  {text: "Menjual produk Rp5.000.000 tunai. Akun kredit yang benar?", options:["Kas","Pendapatan","Modal","Piutang"], correct:"Pendapatan"},
  {text: "Membayar gaji karyawan Rp1.500.000. Akun debit yang benar?", options:["Gaji","Kas","Modal","Perlengkapan"], correct:"Gaji"}
];

let playerHp = 100;
let enemyHp = 100;
let currentQ = 0;
let timerValue = 15;
let timerInterval;

const questionText = document.getElementById("questionText");
const optionsDiv = document.getElementById("options");
const feedback = document.getElementById("feedback");
const playerHpBar = document.getElementById("playerHpBar");
const enemyHpBar = document.getElementById("enemyHpBar");
const timerEl = document.getElementById("timer");

function startTimer(){
  clearInterval(timerInterval);
  timerValue = 15;
  timerEl.textContent = `Waktu tersisa: ${timerValue} detik`;
  timerInterval = setInterval(()=>{
    timerValue--;
    timerEl.textContent = `Waktu tersisa: ${timerValue} detik`;
    if(timerValue <= 0){
      clearInterval(timerInterval);
      feedback.textContent = `⏰ Waktu habis! Kamu kehilangan 15 HP!`;
      playerHp -= 15;
      if(playerHp < 0) playerHp = 0;
      playerHpBar.style.width = playerHp + "%";
      nextQuestion();
    }
  },1000);
}

function loadQuestion(index){
  startTimer();
  const q = questions[index];
  questionText.textContent = q.text;
  optionsDiv.innerHTML = "";
  feedback.textContent = "";
  q.options.forEach(opt=>{
    const btn = document.createElement("button");
    btn.textContent = opt;
    btn.onclick = () => checkAnswer(opt);
    optionsDiv.appendChild(btn);
  });
}

function checkAnswer(selected){
  clearInterval(timerInterval);
  const q = questions[currentQ];
  if(selected === q.correct){
    feedback.textContent = "✅ Benar! Lawan kehilangan 25 HP!";
    enemyHp -= 25;
    if(enemyHp < 0) enemyHp = 0;
    enemyHpBar.style.width = enemyHp + "%";
  } else {
    feedback.textContent = `❌ Salah! Kamu kehilangan 15 HP! Jawaban benar: ${q.correct}`;
    playerHp -= 15;
    if(playerHp < 0) playerHp = 0;
    playerHpBar.style.width = playerHp + "%";
  }

  setTimeout(()=>nextQuestion(),1200);
}

function nextQuestion(){
  if(playerHp <=0){
    feedback.textContent = "💀 Kamu kalah!";
    questionText.textContent = "";
    optionsDiv.innerHTML = "";
    timerEl.textContent = "";
  } else if(enemyHp <=0){
    feedback.textContent = "🏆 Kamu menang!";
    questionText.textContent = "";
    optionsDiv.innerHTML = "";
    timerEl.textContent = "";
  } else {
    currentQ = (currentQ+1) % questions.length;
    loadQuestion(currentQ);
  }
}

// Load soal pertama
loadQuestion(currentQ);
</script>

</body>
</html>
