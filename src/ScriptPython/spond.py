import asyncio
from spond import spond

username = 'soulardleny@gmail.com'
password = 'joupette24**'
group_id = 'A98196A7DF8945D0894604F598B7E545'  # fake 

async def main():
    s = spond.Spond(username=username, password=password)
    groups = await s.get_event_attendance_xlsx('A47B9A95C5B141C2A0D29EA146A11709')
    print(groups)
    await s.clientsession.close()

asyncio.run(main())
