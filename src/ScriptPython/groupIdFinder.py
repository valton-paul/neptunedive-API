import asyncio
from spond import spond
import argparse

# Identifiants et paramètres de connexion
parser = argparse.ArgumentParser(description="Spond Script")
parser.add_argument("--username", required=True, help="Votre email Spond")
parser.add_argument("--password", required=True, help="Votre mot de passe Spond")
parser.add_argument("--group_name", required=True, help="Votre nom de groupe Spond")
args = parser.parse_args()

class GroupNotFoundException(Exception):
    """Exception levée lorsque le groupe avec le nom spécifié n'est pas trouvé."""
    pass

async def main(username, password, group_name_to_find):
    # Initialisation du client Spond
    s = spond.Spond(username=username, password=password)

    try:
        # Récupérer tous les groupes
        groups = await s.get_groups()

        # Rechercher le group_id par le nom du groupe
        group_id = None
        for group in groups:
            if group['name'] == group_name_to_find:
                group_id = group['id']
                break

        if group_id:
            print(f"{group_id}")
        else:
            raise GroupNotFoundException(f"Le groupe '{group_name_to_find}' n'a pas été trouvé.")

    except GroupNotFoundException as e:
        print(f"Error group : {e}")

    except Exception as e:
        print(f"Error : {e}")

    finally:
        # Fermer la session
        await s.clientsession.close()

# Lancer la fonction principale
asyncio.run(main(args.username, args.password, args.group_name))
