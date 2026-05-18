import os
os.environ.setdefault("DJANGO_SETTINGS_MODULE", "impress.settings")
os.environ.setdefault("DJANGO_CONFIGURATION", "Production")

import configurations
configurations.setup()

from core.models import User

email = os.environ["ADMIN_EMAIL"]
password = os.environ["ADMIN_PASSWORD"]

try:
    user = User.objects.get(email=email)
    changed = False
    if not user.is_staff:
        user.is_staff = True
        changed = True
    if not user.is_superuser:
        user.is_superuser = True
        changed = True
    if getattr(user, "admin_email", None) != email:
        user.admin_email = email
        changed = True
    if changed:
        user.save()
        print(f"Promoted to admin: {email}")
    else:
        print(f"Already admin: {email}")
except User.DoesNotExist:
    user = User(email=email, admin_email=email, is_staff=True, is_superuser=True)
    user.set_password(password)
    user.save()
    print(f"Created admin: {email}")
