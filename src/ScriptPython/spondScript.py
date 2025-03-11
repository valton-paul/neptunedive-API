import asyncio
import csv
from spond import spond
from datetime import datetime, timedelta
import tempfile
import argparse


# Identifiants et paramètres de connexion


parser = argparse.ArgumentParser(description="Spond Script")
parser.add_argument("--username", required=True, help="Votre email Spond")
parser.add_argument("--password", required=True, help="Votre mot de passe Spond")
parser.add_argument("--group_id", required=True, help="Votre ID de groupe Spond")
parser.add_argument("--date", required=False, help="Date de début de recherche (format : YYYY-MM-DD)")
args = parser.parse_args()

csv_file_path = tempfile.mktemp(suffix='.csv', dir="/var/www/html/neptunedive/src/ScriptPython/tmpCSV")  # Chemin du fichier CSV

async def main(username, password, group_id, date):
    # Initialisation du client Spond
    try:
        s = spond.Spond(username=username, password=password)
        if date:
            day = datetime.strptime(date, '%Y-%m-%d')
        else:
            day = datetime.utcnow() - timedelta(days=365)
        event_data = await s.get_events(group_id, min_start=day)

        # Vérifier si des événements sont récupérés
        if not event_data:
            print("Aucun événement trouvé.")
            return

        # Liste pour stocker les événements filtrés
        events_to_write = []

        for event in event_data:
            # Vérifier si les participants ont accepté l'invitation
            accepted_participants = []
            if 'responses' in event and 'acceptedIds' in event['responses']:
                accepted_ids = event['responses']['acceptedIds']
                for member in event.get('recipients', {}).get('group', {}).get('members', []):
                    if member['id'] in accepted_ids:
                        accepted_participants.append(f"{member['firstName']} {member['lastName']}")

            # Séparer la date et l'heure de 'startTimestamp'
            start_timestamp = event['endTimestamp']
            start_datetime = datetime.strptime(start_timestamp, '%Y-%m-%dT%H:%M:%SZ')
            event_date = start_datetime.date()
            event_time = start_datetime.time()
            lieu = ''
            if 'location' in event:
                if 'feature' in event['location']:
                    lieu = event['location']['feature']
                if 'address' in event['location']:
                    lieu = lieu +", "+event['location']['address']

            # Extraire les informations pertinentes pour l'événement
            event_info = {
                'Titre': event['heading'],  # Titre de l'événement
                'ID': event['id'],
                'Lieu': lieu,  # Lieu de l'événement
                'Date': event_date,  # La date seule
                'Heure': event_time,  # L'heure seule
                'Participants': ', '.join(accepted_participants)  # Liste des participants
            }
            events_to_write.append(event_info)

        # Sauvegarder les événements dans un fichier CSV
        with open(csv_file_path, mode='w', newline='', encoding='utf-8') as csv_file:
            fieldnames = ['Titre', 'ID', 'Lieu', 'Date', 'Heure', 'Participants']
            writer = csv.DictWriter(csv_file, fieldnames=fieldnames)
            writer.writeheader()  # Écrire les en-têtes
            for event in events_to_write:
                writer.writerow(event)  # Écrire chaque événement

        print(f"{csv_file_path}")

    except Exception as e:
        print(f"Erreur : {e}")

    finally:
        # Fermer la session
        await s.clientsession.close()

# Lancer la fonction principale
asyncio.run(main(args.username, args.password, args.group_id, args.date))



