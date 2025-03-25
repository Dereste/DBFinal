<div class="bg-white p-6 shadow-md rounded-lg">
    <h2 class="text-2xl font-semibold text-gray-700 mb-4">User List</h2>

    @if (session()->has('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
            {{ session('error') }}
        </div>
    @endif

    <table class="w-full border-collapse border border-gray-300">
        <thead>
        <tr class="bg-gray-100 text-center">
            <th class="border border-gray-300 px-4 py-2">Username</th>
            <th class="border border-gray-300 px-4 py-2">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
            <tr class="hover:bg-gray-50 text-center">
                <td class="border border-gray-300 px-4 py-2">{{ $user->UserName }}</td>
                <td class="border border-gray-300 px-4 py-2">
                    <div class="flex justify-center gap-2">
                        <button class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-700"
                                onclick="Livewire.dispatch('deleteUser', { userId: {{ $user->id }} })">
                            Delete
                        </button>

                        <button class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-700"
                                onclick="togglePasswordInput({{ $user->id }})">
                            Reset Password
                        </button>
                    </div>

                    <div id="passwordInput-{{ $user->id }}" class="hidden mt-2">
                        <input type="text" id="passwordField-{{ $user->id }}"
                               placeholder="New Password"
                               class="border border-gray-300 px-2 py-1 rounded-md">
                        <button onclick="resetUserPassword({{ $user->id }})"
                                class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-800">
                            Confirm
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<script>
    function togglePasswordInput(userId) {
        let inputDiv = document.getElementById(`passwordInput-${userId}`);
        inputDiv.classList.toggle('hidden');
    }

    function resetUserPassword(userId) {
        let passwordField = document.getElementById(`passwordField-${userId}`);
        let newPassword = passwordField.value.trim();

        if (!newPassword) {
            alert("Password cannot be empty.");
            return;
        }

        Livewire.dispatch('resetPassword', { userId: userId, newPassword: newPassword });
    }
</script>
