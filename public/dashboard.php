<?php
require_once '../config/database.php';
require_once '../utils/auth_check.php';
require_once '../utils/folder_tree.php';

/* ================= CURRENT FOLDER ================= */
$currentFolder = $_GET['folder'] ?? null;
$parentFolder = null;

if ($currentFolder !== null) {
    $stmt = $pdo->prepare(
        "SELECT parent_id
         FROM folders
         WHERE id = ? AND user_id = ?"
    );
    $stmt->execute([$currentFolder, $_SESSION['user_id']]);
    $parentFolder = $stmt->fetchColumn();
}

/* ================= DIRECT CHILD FOLDERS ================= */
if ($currentFolder !== null) {
    $stmt = $pdo->prepare(
        "SELECT * FROM folders
         WHERE user_id = ? AND parent_id = ?"
    );
    $stmt->execute([$_SESSION['user_id'], $currentFolder]);
} else {
    $stmt = $pdo->prepare(
        "SELECT * FROM folders
         WHERE user_id = ? AND parent_id IS NULL"
    );
    $stmt->execute([$_SESSION['user_id']]);
}
$folders = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= OWN FILES ================= */
if ($currentFolder) {
    $stmt = $pdo->prepare(
        "SELECT * FROM files
         WHERE user_id = ? AND folder_id = ?"
    );
    $stmt->execute([$_SESSION['user_id'], $currentFolder]);
} else {
    $stmt = $pdo->prepare(
        "SELECT * FROM files
         WHERE user_id = ? AND folder_id IS NULL"
    );
    $stmt->execute([$_SESSION['user_id']]);
}
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= SHARED FILES ================= */
$stmt = $pdo->prepare(
    "SELECT f.*, u.username AS owner_name
     FROM files f
     JOIN file_shares s ON f.id = s.file_id
     JOIN users u ON f.user_id = u.id
     WHERE s.shared_with = ?"
);
$stmt->execute([$_SESSION['user_id']]);
$sharedFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= ACTIVITY ================= */
$stmt = $pdo->prepare(
    "SELECT action, file_name, target_user, created_at
     FROM activity_log
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 30"
);
$stmt->execute([$_SESSION['user_id']]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= FOLDER TREE ================= */
$folderTree = getFolders($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Secure Vault | Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">

<style>
body{display:flex;height:100vh;background:#020617}
.sidebar{width:260px;padding:20px;border-right:1px solid rgba(255,255,255,.08);overflow-y:auto}
.sidebar h2{color:#38bdf8;margin-bottom:20px}
.sidebar a{display:block;padding:10px;border-radius:10px;color:#e5e7eb;margin-bottom:6px;cursor:pointer}
.sidebar a.active,.sidebar a:hover{background:rgba(56,189,248,.15);color:#38bdf8}

.folder-tree ul{list-style:none;padding-left:15px}
.folder-tree li span{display:block;padding:6px;border-radius:6px;cursor:pointer}
.folder-tree li span:hover{background:rgba(56,189,248,.15)}

.main{flex:1;display:flex;flex-direction:column}
.topbar{height:64px;padding:0 25px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,.08)}
.content{padding:25px;overflow-y:auto}

.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px}
.file-card{background:rgba(255,255,255,.05);border-radius:14px;padding:18px}
.file-name{margin:10px 0;font-weight:500;word-break:break-word}
.file-actions{display:flex;gap:8px;flex-wrap:wrap}
.delete-btn{background:#ef4444;color:#fff}

.share-box{margin-top:10px}
.suggestions{background:#020617;border-radius:8px;margin-top:6px;overflow:hidden}
.suggestions div{padding:8px;cursor:pointer}
.suggestions div:hover{background:rgba(56,189,248,.2)}
details summary{cursor:pointer;color:#38bdf8;margin-top:8px}

.page{display:none}
.page.active{display:block}

.new-item{padding:10px 14px;cursor:pointer}
.new-item:hover{background:rgba(56,189,248,.15)}
</style>
</head>

<body>
<div class="sidebar">
    <center><img src="logo.png" alt="Logo" style="width:150px;margin-bottom:5px"></center>
    <center><h2>VaultX</h2></center>
    <a class="active" onclick="showPage('drive',this)">My Drive</a>
    <a onclick="showPage('shared',this)">Shared with me</a>
    <a onclick="showPage('activity',this)">Activity</a>
    <a href="../auth/logout.php">Logout</a>

    <hr style="margin:15px 0;border-color:rgba(255,255,255,.1)">
</div>
<div class="main">
<div class="topbar">
    <h1>My Drive</h1>

    <div style="display:flex;gap:15px;align-items:center">
        <input id="searchBox" placeholder="Search files"
               style="padding:8px 12px;border-radius:8px;background:#020617;
               color:#e5e7eb;border:1px solid rgba(255,255,255,.2)">
        <div style="position:relative">
            <button onclick="toggleNewMenu()">➕ New</button>
            <div id="newMenu" style="
                display:none;
                position:absolute;
                top:45px;
                left:0px;
                background:#020617;
                border:1px solid rgba(255,255,255,.15);
                border-radius:10px;
                width:160px;
                z-index:1000;">
                <div onclick="openPicker()" class="new-item">📄 Upload file</div>
                <div onclick="openFolderModal()" class="new-item">📁 New folder</div>
            </div>
        </div>
    </div>
</div>

<div class="content">
<form id="uploadForm" action="../storage/upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" id="fileInput" hidden>
    <input type="hidden" name="folder_id" value="<?= htmlspecialchars($currentFolder) ?>">

    <div id="dropZone"
         style="margin-top:15px;padding:25px;border:2px dashed rgba(56,189,248,.4);
         border-radius:14px;text-align:center">
        Drag & drop files here
    </div>

    <progress id="progressBar" max="100" value="0"
              style="width:100%;display:none"></progress>
</form>
<div id="drive" class="page active">
    <h2>Your Files</h2>
        <?php if ($currentFolder !== null): ?>
            <button onclick="goBack()">🔙 Back</button>
        <?php endif; ?>
    <div class="grid">
        <!-- FOLDERS -->
<?php foreach ($folders as $folder): ?>
    <div class="file-card"
         onclick="openFolder(<?= $folder['id'] ?>)"
         style="cursor:pointer">
        <div style="font-size:36px">📁</div>
        <div class="file-name">
            <?= htmlspecialchars($folder['name']) ?>
        </div>
    </div>
<?php endforeach; ?>

    <?php foreach ($files as $f): ?>
        <div class="file-card" data-name="<?= strtolower($f['original_name']) ?>">
            <div>📄</div>
            <div class="file-name"><?= htmlspecialchars($f['original_name']) ?></div>

            <div class="file-actions">
                <a href="../storage/download.php?id=<?= $f['id'] ?>">Download</a>
                <form action="../storage/delete.php" method="post"
                      onsubmit="return confirm('Delete permanently?')">
                    <input type="hidden" name="file_id" value="<?= $f['id'] ?>">
                    <button class="delete-btn">Delete</button>
                </form>
            </div>
            <div class="share-box">
                <input placeholder="Share with user"
                       oninput="suggestUser(this,<?= $f['id'] ?>)">
                <div class="suggestions"></div>
                <form action="../storage/share.php" method="post" class="shareForm">
                    <input type="hidden" name="file_id" value="<?= $f['id'] ?>">
                    <input type="hidden" name="username">
                </form>
            </div>
            <?php
            $s = $pdo->prepare(
                "SELECT fs.shared_with, u.username
                 FROM file_shares fs
                 JOIN users u ON fs.shared_with = u.id
                 WHERE fs.file_id = ? AND fs.owner_id = ?"
            );
            $s->execute([$f['id'], $_SESSION['user_id']]);
            $sharedUsers = $s->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php if ($sharedUsers): ?>
                <details>
                    <summary>Shared with (<?= count($sharedUsers) ?>)</summary>
                    <?php foreach ($sharedUsers as $su): ?>
                        <div style="display:flex;justify-content:space-between">
                            <?= htmlspecialchars($su['username']) ?>
                            <form action="../storage/unshare.php" method="post">
                                <input type="hidden" name="file_id" value="<?= $f['id'] ?>">
                                <input type="hidden" name="shared_with" value="<?= $su['shared_with'] ?>">
                                <button>✕</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </details>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<!-- ================= SHARED ================= -->
<div id="shared" class="page">
    <h2>Shared with you</h2>

    <div class="grid">
    <?php foreach ($sharedFiles as $sf): ?>
        <div class="file-card">
            <div>👥</div>
            <div class="file-name"><?= htmlspecialchars($sf['original_name']) ?></div>
            <div style="font-size:13px;color:#9ca3af">
                Shared by <strong><?= htmlspecialchars($sf['owner_name']) ?></strong>
            </div>
            <a href="../storage/download.php?id=<?= $sf['id'] ?>">Download</a>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<!-- ================= ACTIVITY ================= -->
<div id="activity" class="page">
    <h2>Activity</h2>

    <?php if (!$activities): ?>
        <p style="color:#9ca3af;">No recent activity.</p>
    <?php endif; ?>

    <ul>
    <?php foreach ($activities as $a): ?>
        <li style="padding:12px;border-bottom:1px solid rgba(255,255,255,.08)">
            <strong><?= ucfirst($a['action']) ?></strong>
            <?php if ($a['file_name']): ?>
                <span> <?= htmlspecialchars($a['file_name']) ?></span>
            <?php endif; ?>
            <?php if ($a['target_user']): ?>
                <span> with <?= htmlspecialchars($a['target_user']) ?></span>
            <?php endif; ?>
            <div style="font-size:12px;color:#9ca3af">
                <?= date('d M Y, h:i A', strtotime($a['created_at'])) ?>
            </div>
        </li>
    <?php endforeach; ?>
    </ul>
</div>

</div>
</div>

<!-- ================= CREATE FOLDER MODAL ================= -->
<div id="folderModal" style="
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.6);
    align-items:center;
    justify-content:center;
    z-index:2000;">
    <form action="../storage/create_folder.php" method="post"
          style="background:#020617;padding:25px;border-radius:14px;width:320px">
        <h3 style="margin-bottom:15px;color:#38bdf8">Create folder</h3>

        <input type="text" name="folder_name" placeholder="Folder name" required
               style="width:100%;padding:10px;margin-bottom:15px">

        <input type="hidden" name="parent_id" value="<?= htmlspecialchars($currentFolder) ?>">

        <div style="display:flex;justify-content:flex-end;gap:10px">
            <button type="button" onclick="closeFolderModal()">Cancel</button>
            <button type="submit">Create</button>
        </div>
    </form>
</div>

<!-- ================= JS ================= -->
<script>
function openFolder(id){window.location='dashboard.php?folder='+id}

/* PAGE SWITCH */
function showPage(id,el){
    document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    document.querySelectorAll('.sidebar a').forEach(a=>a.classList.remove('active'));
    el.classList.add('active');
}

/* SEARCH */
searchBox.oninput=()=> {
    document.querySelectorAll('.file-card').forEach(c=>{
        c.style.display=c.dataset.name?.includes(searchBox.value.toLowerCase())?'block':'none';
    });
};

/* UPLOAD */
function openPicker(){fileInput.click()}
fileInput.onchange=()=>upload()

function openFolder(id){
    window.location='dashboard.php?folder='+id;
}

function goBack(){
<?php if ($parentFolder === null): ?>
    window.location='dashboard.php';
<?php else: ?>
    window.location='dashboard.php?folder=<?= $parentFolder ?>';
<?php endif; ?>
}

dropZone.ondragover=e=>{e.preventDefault();dropZone.style.background='rgba(56,189,248,.1)'}
dropZone.ondragleave=()=>dropZone.style.background=''
dropZone.ondrop=e=>{
    e.preventDefault()
    dropZone.style.background=''
    fileInput.files=e.dataTransfer.files
    upload()
}

function upload(){
    let xhr=new XMLHttpRequest()
    let fd=new FormData(uploadForm)
    progressBar.style.display='block'
    xhr.upload.onprogress=e=>progressBar.value=(e.loaded/e.total)*100
    xhr.onload=()=>location.reload()
    xhr.open('POST',uploadForm.action)
    xhr.send(fd)
}

/* USER SUGGESTION */
function suggestUser(input,fileId){
    let box=input.nextElementSibling
    if(!input.value){box.innerHTML='';return}
    fetch('../utils/user_search.php?q='+encodeURIComponent(input.value))
    .then(r=>r.json()).then(users=>{
        box.innerHTML=''
        users.forEach(u=>{
            let d=document.createElement('div')
            d.textContent=u
            d.onclick=()=>{
                let f=input.parentElement.querySelector('.shareForm')
                f.username.value=u
                f.submit()
            }
            box.appendChild(d)
        })
    })
}

/* NEW MENU + MODAL */
function toggleNewMenu(){
    const m=document.getElementById('newMenu')
    m.style.display = m.style.display==='block' ? 'none' : 'block'
}
function openFolderModal(){
    document.getElementById('newMenu').style.display='none'
    document.getElementById('folderModal').style.display='flex'
}
function closeFolderModal(){
    document.getElementById('folderModal').style.display='none'
}
document.addEventListener('click',e=>{
    if(!e.target.closest('#newMenu') && !e.target.closest('button')){
        const m=document.getElementById('newMenu')
        if(m) m.style.display='none'
    }
})
</script>

</body>
</html>
