using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

using Microsoft.Xna.Framework;
using Microsoft.Xna.Framework.Content;
using Microsoft.Xna.Framework.Media;
using Microsoft.Xna.Framework.Input;
using Microsoft.Xna.Framework.Graphics;

namespace MummyMazeBasic
{
    public class ActorManager
    {
        private static ActorManager instance;

        // List of actor
        Actor mummyStupid;
        Actor explorer;
        Actor mummyIntelligent;
        public int[][] local;
        //Content manager
        public ContentManager Content { get; set; }

        /// <summary>
        /// Output instance
        /// </summary>
        public static ActorManager Instance
        {
            get
            {
                if (instance == null)
                {
                    instance = new ActorManager();
                }
                return instance;
            }
        }

        /// <summary>
        /// Initial actor
        /// </summary>
        public ActorManager()
        {
            //Stupid Mummy
            mummyStupid = new Mummy();
            explorer = new Explorer();
            mummyIntelligent = new MummyIntelligent();
            // Explorer human

        }

        public void LoadContent(ContentManager Content)
        {
            this.Content = new ContentManager(Content.ServiceProvider, "Content");
            mummyStupid.LoadContent();
            explorer.LoadContent();
            mummyIntelligent.LoadContent();
        }

        public void UploadConten()
        {
            mummyStupid.UploadConten();
            explorer.UploadConten();
            mummyIntelligent.UploadConten();
        }

        public void Update(GameTime gameTime)
        {
            mummyStupid.Update(gameTime);
            explorer.Update(gameTime);
            mummyIntelligent.Update(gameTime);
        }

        public void Draw(SpriteBatch spriteBatch)
        {
            mummyStupid.Draw(spriteBatch);
            explorer.Draw(spriteBatch);
            mummyIntelligent.Draw(spriteBatch);
        }
    }
}

