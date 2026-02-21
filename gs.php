<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Simulasi Kejuruan Interaktif</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    body { font-family: 'Inter', sans-serif; }
    .tool { transition: transform 0.1s, background-color 0.1s; box-shadow:0 4px 6px rgba(0,0,0,0.1);}
    .tool:active{transform:translateY(1px);box-shadow:0 2px 4px rgba(0,0,0,0.1);}
    #startButton:disabled{cursor:not-allowed;}
</style>
</head>
<body class="bg-teal-50 min-h-screen flex items-center justify-center p-4">

<div id="appContainer" class="bg-white p-6 md:p-10 rounded-2xl shadow-xl w-full max-w-xl">
<h1 class="text-3xl font-bold text-teal-700 mb-2">Simulasi Kejuruan Interaktif</h1>
<p class="text-gray-500 mb-6">SMK Diagnostik</p>

<form id="simulationForm">
    <label for="department" class="font-semibold mb-2 block text-gray-700">Pilih Jurusan/Bidang:</label>
    <select name="department" id="department" class="w-full p-3 rounded-lg border border-gray-300 mb-4 focus:ring-teal-500 focus:border-teal-500 appearance-none bg-white">
        <option value="" disabled selected>-- Pilih Jurusan --</option>
        <option value="TKR">Teknik Kendaraan Ringan (TKR)</option>
        <option value="TBSM">Teknik Bisnis Sepeda Motor (TBSM)</option>
        <option value="TKJ">Teknik Komputer dan Jaringan (TKJ)</option>
        <option value="ATPH">Agribisnis Tanaman Pangan dan Hortikultura (ATPH)</option>
        <option value="AKL">Akuntansi dan Keuangan Lembaga (AKL)</option>
        <option value="TAB">Teknik Alat Berat</option>
        <option value="Umum">Umum/Lainnya</option>
    </select>

    <label for="simulation" class="font-semibold mb-2 block text-gray-700">Pilih Simulasi Diagnostik:</label>
    <select name="simulation" id="simulation" class="w-full p-3 rounded-lg border border-gray-300 mb-3 focus:ring-teal-500 focus:border-teal-500 appearance-none bg-white">
        <option value="" disabled selected>-- Pilih Jenis Kerusakan --</option>
    </select>

    <div class="flex items-center my-3">
        <div class="flex-grow border-t border-gray-300"></div>
        <span class="flex-shrink mx-4 text-gray-500 text-sm font-medium">ATAU MASUKKAN KASUS KUSTOM</span>
        <div class="flex-grow border-t border-gray-300"></div>
    </div>

    <label for="customSimulationInput" class="font-semibold mb-2 block text-gray-700">Tuliskan Kasus Diagnostik:</label>
    <input type="text" id="customSimulationInput" placeholder="Contoh: Lampu rem tidak menyala atau Selisih Kas Kecil" class="w-full p-3 rounded-lg border border-gray-300 mb-4 focus:ring-teal-500 focus:border-teal-500">

    <button type="submit" id="startButton" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded-lg transition-colors disabled:bg-gray-400">Mulai Simulasi</button>
</form>

<div id="gameArea" class="mt-6"></div>
</div>

<script>
let levels=[], dynamicTools=[], currentLevel=0, score=0, isProcessing=false;

const form=document.getElementById('simulationForm');
const gameArea=document.getElementById('gameArea');
const startButton=document.getElementById('startButton');
const simulationSelect=document.getElementById('simulation');
const departmentSelect=document.getElementById('department'); 
const customInput=document.getElementById('customSimulationInput');

// Simulasi options dropdown
const simulationOptionsMap = {
    "TKR":[
        {value:"mobil_mati",label:"Mobil Mati Total"},
        {value:"mesin_misfire",label:"Mesin Misfire"},
        {value:"rem_tidak_pakem",label:"Rem Tidak Pakem"}
    ],
    "TBSM":[
        {value:"motor_tidak_hidup",label:"Motor Tidak Mau Hidup"}
    ],
    "TKJ":[
        {value:"server_down",label:"Server Down"}
    ],
    "ATPH":[
        {value:"hama_tanaman",label:"Hama Tanaman"}
    ],
    "AKL":[
        {value:"jurnal_tidak_balance",label:"Jurnal Tidak Balance"}
    ],
    "TAB":[
        {value:"engine_overheat",label:"Engine Overheat"}
    ],
    "Umum":[
        {value:"electrical_general",label:"Kelistrikan Umum"}
    ]
};

function updateSimulationOptions(departmentValue){
    const options=simulationOptionsMap[departmentValue]||[];
    simulationSelect.innerHTML='<option value="" disabled selected>-- Pilih Jenis Kerusakan --</option>';
    options.forEach(opt=>{
        const o=document.createElement('option');
        o.value=opt.value; o.textContent=opt.label;
        simulationSelect.appendChild(o);
    });
    updateButtonState();
}

const updateButtonState=()=>{
    const deptVal=departmentSelect.value;
    const dropVal=simulationSelect.value;
    const customVal=customInput.value.trim();
    startButton.disabled=!(deptVal&&(dropVal||customVal));
};

form.addEventListener('submit', async(e)=>{
    e.preventDefault();
    const deptVal=departmentSelect.value;
    const simType=customInput.value.trim()||simulationSelect.value;
    if(deptVal&&simType){
        gameArea.innerHTML='';
        await fetchLevels(deptVal, simType);
    }
});

departmentSelect.addEventListener('change', ()=>{
    updateSimulationOptions(departmentSelect.value);
});
simulationSelect.addEventListener('change',updateButtonState);
customInput.addEventListener('input',updateButtonState);

// Ambil level + tools dari backend Gemini
async function fetchLevels(department, simulationType){
    if(isProcessing) return;
    isProcessing=true;
    startButton.disabled=true;
    startButton.textContent='Memuat Level...';
    gameArea.innerHTML=`<div class="text-center p-4 text-teal-600">Memuat soal dari AI...</div>`;
    try{
        const res=await fetch('fetch_levels.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({department,simulationType})
        });
        const data=await res.json();
        if(data.error) throw new Error(data.error);
        levels=data.levels;
        dynamicTools=data.tools;
        currentLevel=0;
        score=0;
        loadLevel(currentLevel);
    }catch(err){
        gameArea.innerHTML=`<div class="p-4 bg-red-100 text-red-700 rounded-lg">❌ Gagal memuat simulasi: ${err.message}</div>`;
    }finally{
        isProcessing=false;
        startButton.disabled=false;
        startButton.textContent='Mulai Simulasi';
    }
}

// Render level
function loadLevel(idx){
    if(idx>=levels.length){
        gameArea.innerHTML=`<div class="p-4 bg-green-100 text-green-800 rounded-lg">
        🎉 Semua level selesai! Skor akhir: ${score}/${levels.length}
        </div>`;
        return;
    }
    const level=levels[idx];
    let html=`<div class="mb-4 font-semibold text-gray-800">Level ${idx+1}: ${level.problem}</div>`;
    html+=`<div class="grid grid-cols-2 gap-3">`;
    dynamicTools.forEach(tool=>{
        html+=`<button class="tool p-3 bg-teal-100 hover:bg-teal-200 rounded-lg font-medium" onclick="checkAnswer('${tool.id}')">${tool.label}</button>`;
    });
    html+='</div>';
    gameArea.innerHTML=html;
}

// Cek jawaban
function checkAnswer(selectedId){
    const level=levels[currentLevel];
    let feedbackDiv=document.createElement('div');
    feedbackDiv.className='mt-3 p-3 rounded-lg '+(selectedId===level.correct?'bg-green-100 text-green-800':'bg-red-100 text-red-800');
    feedbackDiv.textContent=level.feedback;
    gameArea.appendChild(feedbackDiv);
    if(selectedId===level.correct) score++;
    currentLevel++;
    setTimeout(()=>loadLevel(currentLevel),1200);
}
</script>

</body>
</html>
