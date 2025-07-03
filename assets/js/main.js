// লাইক বাটন ফাংশনালিটি
document.addEventListener('DOMContentLoaded', function() {
    // লাইক বাটন
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const poemId = this.dataset.poemId;
            const isLiked = this.classList.contains('liked');
            
            fetch(`api/poems.php?action=like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${poemId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.toggle('liked', data.isLiked);
                    const likeCount = this.querySelector('.like-count');
                    if (likeCount) {
                        likeCount.textContent = data.likes;
                    }
                    
                    // হৃদয় আইকনে অ্যানিমেশন
                    const heartIcon = this.querySelector('i');
                    heartIcon.style.transform = 'scale(1.5)';
                    setTimeout(() => {
                        heartIcon.style.transform = 'scale(1)';
                    }, 300);
                } else if (data.error) {
                    alert(data.error);
                }
            });
        });
    });
    
    // শেয়ার বাটন
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const poemId = this.dataset.poemId;
            const poemUrl = `${window.location.origin}/poem.php?id=${poemId}`;
            const poemTitle = document.querySelector('.poem-title')?.textContent || 'অন্তঃকণ্ঠ - বাংলা কবিতা';
            
            // সোশ্যাল মিডিয়া শেয়ার
            if (navigator.share) {
                navigator.share({
                    title: poemTitle,
                    text: 'এই সুন্দর কবিতাটি পড়ুন',
                    url: poemUrl
                }).then(() => {
                    console.log('সফলভাবে শেয়ার করা হয়েছে');
                }).catch(err => {
                    console.error('শেয়ার করতে সমস্যা:', err);
                    copyToClipboard(poemUrl);
                });
            } else {
                // ফ্যালব্যাক - লিংক কপি করুন
                copyToClipboard(poemUrl);
            }
        });
    });
    
    // লিংক কপি করার ফাংশন
    function copyToClipboard(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            const successful = document.execCommand('copy');
            const msg = successful ? 'লিংক কপি করা হয়েছে!' : 'কপি করতে সমস্যা হয়েছে';
            alert(`${msg}\n\n${text}`);
        } catch (err) {
            alert('লিংক কপি করতে সমস্যা: ' + err);
        }
        
        document.body.removeChild(textarea);
    }
});