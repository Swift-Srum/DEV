document.getElementById('addStaffForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('../admin/actions.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'add',
                username: formData.get('username'),
                email: formData.get('email'),
                password: formData.get('password'),
                userType: formData.get('userType')
            })
        });
        
        if (response.ok) {
            location.reload();
        } else {
            alert('Failed to add staff member');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
});

async function editStaff(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    const username = row.cells[0].textContent;
    const email = row.cells[1].textContent;
    
    const newUsername = prompt('Enter new username:', username);
    const newEmail = prompt('Enter new email:', email);
    
    if (newUsername && newEmail) {
        try {
            const response = await fetch('/admin/actions.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'edit',
                    id: id,
                    username: newUsername,
                    email: newEmail
                })
            });
            
            if (response.ok) {
                location.reload();
            } else {
                alert('Failed to update staff member');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    }
}

async function toggleStatus(id) {
    try {
        const response = await fetch('/admin/actions.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'toggle',
                id: id
            })
        });
        
        if (response.ok) {
            location.reload();
        } else {
            alert('Failed to toggle status');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
}

async function deleteStaff(id) {
    if (confirm('Are you sure you want to delete this staff member?')) {
        try {
            const response = await fetch('/admin/actions.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'delete',
                    id: id
                })
            });
            
            if (response.ok) {
                location.reload();
            } else {
                alert('Failed to delete staff member');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    }
}

// Update the function name from updateStaffType to updateUserType
async function updateUserType(id, userType) {
    try {
        const response = await fetch('../admin/actions.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'updateUserType',
                id: id,
                userType: userType
            })
        });
        
        if (response.ok) {
            alert('User type updated successfully');
        } else {
            alert('Failed to update user type');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
}