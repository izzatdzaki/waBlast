// Quick JavaScript Syntax Validator
// Copy this to browser console to check for syntax errors

console.log('🔍 Checking JavaScript syntax...');

// Test function definitions
try {
    if (typeof checkBaileysStatus === 'function') {
        console.log('✅ checkBaileysStatus: DEFINED');
    } else {
        console.error('❌ checkBaileysStatus: NOT DEFINED');
    }
} catch (e) {
    console.error('❌ checkBaileysStatus error:', e.message);
}

try {
    if (typeof openBaileysHealth === 'function') {
        console.log('✅ openBaileysHealth: DEFINED');
    } else {
        console.error('❌ openBaileysHealth: NOT DEFINED');
    }
} catch (e) {
    console.error('❌ openBaileysHealth error:', e.message);
}

try {
    if (typeof generateQRCode === 'function') {
        console.log('✅ generateQRCode: DEFINED');
    } else {
        console.error('❌ generateQRCode: NOT DEFINED');
    }
} catch (e) {
    console.error('❌ generateQRCode error:', e.message);
}

try {
    if (typeof showNotification === 'function') {
        console.log('✅ showNotification: DEFINED');
    } else {
        console.error('❌ showNotification: NOT DEFINED');
    }
} catch (e) {
    console.error('❌ showNotification error:', e.message);
}

try {
    if (typeof refreshDevices === 'function') {
        console.log('✅ refreshDevices: DEFINED');
    } else {
        console.error('❌ refreshDevices: NOT DEFINED');
    }
} catch (e) {
    console.error('❌ refreshDevices error:', e.message);
}

console.log('\n📊 Summary:');
console.log('If all show ✅, JavaScript is loaded correctly');
console.log('Refresh page (Ctrl+F5) if any show ❌');
