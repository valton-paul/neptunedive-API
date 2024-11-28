import asyncio
import csv
from spond import spond
from datetime import datetime
import tempfile



# Identifiants et paramètres de connexion
username = 'soulardleny@gmail.com'
password = 'joupette24**'
csv_file_path = tempfile.mktemp(suffix='.csv', dir="/var/www/html/neptunedive/src/ScriptPython/tmpCSV")  # Chemin du fichier CSV
group_id = 'BACCCFEB4849435FA1C5A2591BD028F7'
today = datetime.utcnow()

async def main():
    # Initialisation du client Spond
    s = spond.Spond(username=username, password=password)

    try:
        # Récupérer les événements à partir de la date du jour
        event_data = await s.get_events(group_id, min_start=today)

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

            # Extraire les informations pertinentes pour l'événement
            event_info = {
                'Titre': event['heading'],
                'ID': event['id'],
                'Lieu': event['location']['address'],
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
asyncio.run(main())
