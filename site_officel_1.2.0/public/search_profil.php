<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche d'utilisateur</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; padding: 20px; }
        input[type="text"] { padding: 8px; width: 300px; }
        .suggestions { margin-top: 10px; background: white; border: 1px solid #ccc; max-width: 300px; }
        .suggestion-item {
            display: flex;
            align-items: center;
            padding: 8px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .suggestion-item img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        .suggestion-item:hover { background-color: #f0f0f0; }
    </style>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
</head>
<body>

<h2>Recherche en direct</h2>

<input type="text" id="search" placeholder="Nom d'utilisateur">
<div id="suggestions" class="suggestions"></div>

<script>
$(document).ready(function() {
    $('#search').keyup(function() {
        var query = $(this).val();
        if (query.length >= 2) {
            $.ajax({
                url: '../assets/js/ajax_search.php',
                method: 'POST',
                data: { q: query },
                success: function(data) {
                    $('#suggestions').html('');
                    try {
                        var users = JSON.parse(data);
                        if (users.length > 0) {
                            $.each(users, function(i, user) {
                                var avatar = user.avatar && user.avatar !== '' 
                                    ? '../uploads/avatars/' + user.avatar 
                                    : '../uploads/avatars/default.jpeg';

                                $('#suggestions').append(
                                    '<div class="suggestion-item" onclick="window.location.href=\'profile.php?user_id=' + user.id + '\'">' +
                                    '<img src="' + avatar + '" alt="avatar">' +
                                    '<span>' + user.username + '</span>' +
                                    '</div>'
                                );
                            });
                        } else {
                            $('#suggestions').html('<div class="suggestion-item">Aucun résultat</div>');
                        }
                    } catch (e) {
                        $('#suggestions').html('<div class="suggestion-item">Erreur de réponse</div>');
                    }
                }
            });
        } else {
            $('#suggestions').html('');
        }
    });
});
</script>

</body>
</html>
